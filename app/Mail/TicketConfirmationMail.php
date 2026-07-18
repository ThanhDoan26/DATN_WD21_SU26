<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\QRCodeService;
use App\Services\BarcodeService;
use App\Services\PDFService;
use App\Services\GoogleCalendarService;

class TicketConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    // Chỉ lưu booking (mảng) và showtime (Eloquent Model) để serialize siêu nhẹ trong queue payload.
    public $booking;
    public $showtime;

    // Các biến phụ trợ không được serialize (protected) để tránh đầy dung lượng hàng đợi.
    protected $qrCodeData;
    protected $barcodeData;
    protected $calendarUrlData;
    protected $seatsListData;
    protected $pdfBinaryData;

    /**
     * Create a new message instance.
     */
    public function __construct($booking, $showtime)
    {
        $this->booking = $booking;
        $this->showtime = $showtime;
    }

    /**
     * Tính toán động dữ liệu vé khi tiến hành gửi thư trên Queue worker
     */
    /**
     * Tính toán động dữ liệu vé khi tiến hành gửi thư trên Queue worker
     */
    protected function prepareTicketData(): void
    {
        // Tránh tính toán lại nếu đã chạy
        if ($this->qrCodeData !== null) {
            return;
        }

        \Illuminate\Support\Facades\Log::info("TicketConfirmationMail [Step: Start] - Preparing ticket data for Booking ID: " . ($this->booking['booking_id'] ?? 'N/A'));

        // Safety check if booking is completely null or empty
        if (empty($this->booking)) {
            \Illuminate\Support\Facades\Log::error("TicketConfirmationMail - [Error] booking data is null or empty.");
            $this->qrCodeData = '';
            $this->barcodeData = '';
            $this->calendarUrlData = '';
            $this->seatsListData = '';
            $this->pdfBinaryData = null;
            return;
        }

        // Tải đầy đủ thông tin từ database để có thông tin về combos, mã giảm giá và thông tin liên hệ
        \Illuminate\Support\Facades\Log::info("TicketConfirmationMail [Step: Get Booking Details] - Fetching model details for Booking ID: " . ($this->booking['booking_id'] ?? 'N/A'));
        $bookingModel = null;
        if (!empty($this->booking['booking_id'])) {
            $bookingModel = \App\Models\Booking::with(['combos', 'user'])->find($this->booking['booking_id']);
        }

        if ($bookingModel) {
            // Nạp thêm thông tin chi tiết vào mảng public $booking
            $this->booking['discount_amount'] = (float) $bookingModel->discount_amount;
            $this->booking['customer_name'] = $bookingModel->customer_name ?? $bookingModel->user?->name ?? 'Khách hàng';
            $this->booking['customer_phone'] = $bookingModel->customer_phone ?? $bookingModel->user?->phone ?? '';
            $this->booking['customer_email'] = $bookingModel->customer_email ?? $bookingModel->user?->email ?? '';
            $this->booking['combos'] = $bookingModel->combos ?? collect();
            \Illuminate\Support\Facades\Log::info("TicketConfirmationMail - Successfully loaded booking model details.");
        } else {
            \Illuminate\Support\Facades\Log::warning("TicketConfirmationMail - Booking model not found in DB for ID: " . ($this->booking['booking_id'] ?? 'N/A'));
            $this->booking['discount_amount'] = $this->booking['discount_amount'] ?? 0;
            $this->booking['customer_name'] = $this->booking['customer_name'] ?? 'Khách hàng';
            $this->booking['customer_phone'] = $this->booking['customer_phone'] ?? '';
            $this->booking['customer_email'] = $this->booking['customer_email'] ?? '';
            $this->booking['combos'] = $this->booking['combos'] ?? collect();
        }

        // 1. Sinh danh sách ghế dạng chuỗi (ví dụ: A1, A2, A3)
        $seats = $this->booking['seats'] ?? [];
        $this->seatsListData = collect($seats)
            ->map(fn($s) => isset($s->row_name, $s->seat_number) ? $s->row_name . $s->seat_number : '')
            ->filter()
            ->implode(', ');
        \Illuminate\Support\Facades\Log::info("TicketConfirmationMail - Generated seats list: {$this->seatsListData}");

        // Safety check if showtime is null
        if (empty($this->showtime)) {
            \Illuminate\Support\Facades\Log::error("TicketConfirmationMail - [Error] showtime model is null.");
            $this->qrCodeData = '';
            $this->barcodeData = '';
            $this->calendarUrlData = '';
            $this->pdfBinaryData = null;
            return;
        }

        // 2. Sinh mã QR Code bảo mật kèm checksum ký số
        \Illuminate\Support\Facades\Log::info("TicketConfirmationMail [Step: Generate QR] - Generating QR Code");
        $qrCodeDataRaw = '';
        try {
            $qrService = new QRCodeService();
            $seatIds = collect($seats)->pluck('seat_id')->filter()->toArray();
            $movieId = $this->showtime->movie->id ?? null;
            $showtimeId = $this->showtime->id ?? null;
            $paymentTime = $this->booking['payment_time'] ?? null;

            if ($movieId && $showtimeId) {
                $qrCodeDataRaw = $qrService->generateTicketQRCode(
                    $this->booking['booking_id'],
                    $this->booking['booking_code'] ?? '',
                    $movieId,
                    $showtimeId,
                    $seatIds,
                    $this->seatsListData,
                    $paymentTime
                );
                \Illuminate\Support\Facades\Log::info("TicketConfirmationMail - QR Code generated successfully.");
            } else {
                \Illuminate\Support\Facades\Log::error("TicketConfirmationMail - QRCodeService skipped because movieId or showtimeId is null.");
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("TicketConfirmationMail [Error: QR Generation Failed] - QRCodeService failed: " . $e->getMessage());
        }
        $this->qrCodeData = $qrCodeDataRaw;

        // 3. Sinh Barcode có thể bật/tắt qua config
        \Illuminate\Support\Facades\Log::info("TicketConfirmationMail [Step: Generate Barcode] - Generating Barcode");
        try {
            $barcodeService = new BarcodeService();
            $this->barcodeData = $barcodeService->generateBarcode($this->booking['booking_code'] ?? '');
            \Illuminate\Support\Facades\Log::info("TicketConfirmationMail - Barcode generated successfully.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("TicketConfirmationMail [Error: Barcode Generation Failed] - BarcodeService failed: " . $e->getMessage());
            $this->barcodeData = '';
        }

        // 4. Sinh URL thêm vào Google Calendar
        \Illuminate\Support\Facades\Log::info("TicketConfirmationMail [Step: Generate Calendar] - Generating Google Calendar link");
        try {
            $calendarService = new GoogleCalendarService();
            $movieTitle = $this->showtime->movie->title ?? 'N/A';
            $startTime = $this->showtime->start_time ?? now()->toDateTimeString();
            $duration = $this->showtime->movie->duration ?? 0;
            $cinemaName = $this->showtime->room->cinema->name ?? 'N/A';
            $cinemaAddress = $this->showtime->room->cinema->address ?? 'N/A';
            $roomName = $this->showtime->room->name ?? 'N/A';

            $this->calendarUrlData = $calendarService->generateCalendarUrl(
                $movieTitle,
                $startTime,
                $duration,
                $cinemaName,
                $cinemaAddress,
                $roomName,
                $this->seatsListData,
                $this->booking['booking_code'] ?? ''
            );
            \Illuminate\Support\Facades\Log::info("TicketConfirmationMail - Google Calendar URL generated successfully.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("TicketConfirmationMail [Error: Calendar Generation Failed] - GoogleCalendarService failed: " . $e->getMessage());
            $this->calendarUrlData = '';
        }

        // 5. Sinh dữ liệu nhị phân file PDF Vé Điện Tử
        try {
            \Illuminate\Support\Facades\Log::info("TicketConfirmationMail [Step: Generate PDF] - Generating Ticket PDF");
            $pdfService = new PDFService();
            $this->pdfBinaryData = $pdfService->generateTicketPDF('pdf.ticket-pdf', [
                'booking' => $this->booking,
                'showtime' => $this->showtime,
                'qrCode' => $this->qrCodeData,
                'barcode' => $this->barcodeData,
                'calendarUrl' => $this->calendarUrlData,
                'seatsList' => $this->seatsListData,
            ]);
            \Illuminate\Support\Facades\Log::info("TicketConfirmationMail - Ticket PDF generated successfully.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("TicketConfirmationMail [Error: PDF Generation Failed] - PDFService failed: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->pdfBinaryData = null; // Do not block system
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎬 Đặt vé thành công - ' . ($this->showtime->movie->title ?? 'N/A'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $this->prepareTicketData();

        \Illuminate\Support\Facades\Log::info("TicketConfirmationMail [Step: Render Blade] - Preparing content configuration for emails.ticket-confirmation");

        return new Content(
            view: 'emails.ticket-confirmation',
            with: [
                'qrCode' => $this->qrCodeData,
                'barcode' => $this->barcodeData,
                'calendarUrl' => $this->calendarUrlData,
                'seatsList' => $this->seatsListData,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $this->prepareTicketData();

        if ($this->pdfBinaryData === null) {
            \Illuminate\Support\Facades\Log::warning("TicketConfirmationMail - Skipping PDF attachment because PDF binary data is null.");
            return [];
        }

        return [
            Attachment::fromData(
                fn () => $this->pdfBinaryData,
                'E-Ticket-' . ($this->booking['booking_code'] ?? 'N/A') . '.pdf'
            )->withMime('application/pdf'),
        ];
    }

    /**
     * Send the message using the given mailer.
     *
     * @param  \Illuminate\Contracts\Mail\Factory|\Illuminate\Contracts\Mail\Mailer  $mailer
     * @return \Illuminate\SentMessage|null
     */
    public function send($mailer)
    {
        \Illuminate\Support\Facades\Log::info("TicketConfirmationMail [Step: Send Mail] - Starting mail sending process.");

        // First prepare ticket data so it is fully populated for validation logging
        try {
            $this->prepareTicketData();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("TicketConfirmationMail - [Error] prepareTicketData failed before sending: " . $e->getMessage());
        }

        // Pre-send validation and data check logging
        $bookingId = $this->booking['booking_id'] ?? 'N/A';
        $bookingCode = $this->booking['booking_code'] ?? 'N/A';
        $email = $this->booking['customer_email'] ?? $this->booking['user']['email'] ?? 'N/A';
        $movie = $this->showtime->movie->title ?? 'N/A';
        $showtime = $this->showtime->start_time ?? 'N/A';
        $seats = $this->seatsListData ?? 'N/A';
        $qrExists = !empty($this->qrCodeData) ? 'Yes' : 'No';
        $barcodeExists = !empty($this->barcodeData) ? 'Yes' : 'No';
        $pdfExists = !empty($this->pdfBinaryData) ? 'Yes' : 'No';

        \Illuminate\Support\Facades\Log::info("TicketConfirmationMail - Pre-Send Data Validation Check:", [
            'Booking ID' => $bookingId,
            'Booking Code' => $bookingCode,
            'User Email' => $email,
            'Movie' => $movie,
            'Showtime' => $showtime,
            'Seats' => $seats,
            'QR Code Generated' => $qrExists,
            'Barcode Generated' => $barcodeExists,
            'PDF Generated' => $pdfExists,
        ]);

        try {
            // Render Blade step logging
            \Illuminate\Support\Facades\Log::info("TicketConfirmationMail [Step: Render Blade] - Rendering email view 'emails.ticket-confirmation'");
            
            $result = parent::send($mailer);

            \Illuminate\Support\Facades\Log::info("TicketConfirmationMail [Step: Finish] - Mail sending process completed successfully.");
            return $result;
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            $file = $e->getFile();
            $line = $e->getLine();
            $trace = $e->getTraceAsString();

            // 5. If Blade email error
            if (str_contains($msg, '.blade.php') || str_contains($file, 'views') || str_contains($file, 'Blade') || str_contains($file, 'emails/ticket-confirmation')) {
                $missingVar = 'Unknown';
                if (preg_match('/Undefined variable \$([a-zA-Z0-9_]+)/', $msg, $matches)) {
                    $missingVar = '$' . $matches[1];
                }
                \Illuminate\Support\Facades\Log::error("TicketConfirmationMail [Error: Blade Email Error] - Failed to render blade view 'emails.ticket-confirmation'. View: emails.ticket-confirmation, Missing variable: {$missingVar}", [
                    'message' => $msg,
                    'file' => $file,
                    'line' => $line,
                    'trace' => $trace
                ]);
            } else {
                // 6. If SMTP / Connection / Authentication error
                $smtpErrorCategory = 'Unknown Transport/SMTP Error';
                $lowerMsg = strtolower($msg);
                
                if (str_contains($lowerMsg, 'authenticate') || str_contains($lowerMsg, 'authentication') || str_contains($lowerMsg, '535') || str_contains($lowerMsg, 'auth')) {
                    $smtpErrorCategory = 'Authentication Failed';
                } elseif (str_contains($lowerMsg, 'connection') || str_contains($lowerMsg, 'refused') || str_contains($lowerMsg, 'could not connect') || str_contains($lowerMsg, '10061') || str_contains($lowerMsg, 'active refusal')) {
                    $smtpErrorCategory = 'Connection Failed';
                } elseif (str_contains($lowerMsg, 'timeout') || str_contains($lowerMsg, 'timed out')) {
                    $smtpErrorCategory = 'Timeout';
                } elseif (str_contains($lowerMsg, 'credentials') || str_contains($lowerMsg, 'username and password not accepted')) {
                    $smtpErrorCategory = 'Invalid Credentials';
                }

                \Illuminate\Support\Facades\Log::error("TicketConfirmationMail [Error: SMTP/Transport Error - {$smtpErrorCategory}] - SMTP transmission failed. Category: {$smtpErrorCategory}", [
                    'message' => $msg,
                    'file' => $file,
                    'line' => $line,
                    'trace' => $trace
                ]);
            }

            return null; // Return null to avoid failing queue and keep response clean
        }
    }
}
