<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'Trường :attribute phải được chấp nhận.',
    'accepted_if'          => 'Trường :attribute phải được chấp nhận khi :other là :value.',
    'active_url'           => 'Trường :attribute không phải là một URL hợp lệ.',
    'after'                => 'Trường :attribute phải là một ngày sau ngày :date.',
    'after_or_equal'       => 'Trường :attribute phải là thời gian sau hoặc bằng :date.',
    'alpha'                => 'Trường :attribute chỉ có thể chứa các chữ cái.',
    'alpha_dash'           => 'Trường :attribute chỉ có thể chứa chữ cái, số, dấu gạch ngang và dấu gạch dưới.',
    'alpha_num'            => 'Trường :attribute chỉ có thể chứa chữ cái và số.',
    'array'                => 'Trường :attribute phải là một danh sách (mảng).',
    'ascii'                => 'Trường :attribute chỉ được chứa các ký tự chữ và số byte đơn và ký hiệu.',
    'before'               => 'Trường :attribute phải là một ngày trước ngày :date.',
    'before_or_equal'      => 'Trường :attribute phải là thời gian trước hoặc bằng :date.',
    'between'              => [
        'array'   => 'Trường :attribute phải có từ :min đến :max phần tử.',
        'file'    => 'Dung lượng tập tin :attribute phải từ :min đến :max KB.',
        'numeric' => 'Trường :attribute phải nằm trong khoảng từ :min đến :max.',
        'string'  => 'Trường :attribute phải có từ :min đến :max ký tự.',
    ],
    'boolean'              => 'Trường :attribute phải là đúng hoặc sai.',
    'can'                  => 'Trường :attribute chứa giá trị không được phép.',
    'confirmed'            => 'Giá trị xác nhận trong trường :attribute không khớp.',
    'contains'             => 'Trường :attribute thiếu giá trị bắt buộc.',
    'current_password'     => 'Mật khẩu hiện tại không chính xác.',
    'date'                 => 'Trường :attribute không phải là định dạng ngày hợp lệ.',
    'date_equals'          => 'Trường :attribute phải là một ngày bằng với :date.',
    'date_format'          => 'Trường :attribute không giống với định dạng :format.',
    'decimal'              => 'Trường :attribute phải có :decimal chữ số thập phân.',
    'declined'             => 'Trường :attribute phải bị từ chối.',
    'declined_if'          => 'Trường :attribute phải bị từ chối khi :other là :value.',
    'different'            => 'Trường :attribute và :other phải khác nhau.',
    'digits'               => 'Độ dài của trường :attribute phải gồm :digits chữ số.',
    'digits_between'       => 'Độ dài của trường :attribute phải từ :min đến :max chữ số.',
    'dimensions'           => 'Kích thước hình ảnh trong trường :attribute không hợp lệ.',
    'distinct'             => 'Trường :attribute có giá trị trùng lặp.',
    'doesnt_end_with'      => 'Trường :attribute không được kết thúc bằng một trong các giá trị sau: :values.',
    'doesnt_start_with'    => 'Trường :attribute không được bắt đầu bằng một trong các giá trị sau: :values.',
    'email'                => 'Trường :attribute phải là một địa chỉ email hợp lệ.',
    'ends_with'            => 'Trường :attribute phải kết thúc bằng một trong những giá trị sau: :values.',
    'enum'                 => 'Giá trị đã chọn trong trường :attribute không hợp lệ.',
    'exists'               => 'Giá trị đã chọn trong trường :attribute không tồn tại.',
    'extensions'           => 'Trường :attribute phải có một trong các phần mở rộng sau: :values.',
    'file'                 => 'Trường :attribute phải là một tệp tin.',
    'filled'               => 'Trường :attribute không được bỏ trống.',
    'gt'                   => [
        'array'   => 'Mảng :attribute phải có nhiều hơn :value phần tử.',
        'file'    => 'Dung lượng tập tin :attribute phải lớn hơn :value KB.',
        'numeric' => 'Giá trị :attribute phải lớn hơn :value.',
        'string'  => 'Độ dài :attribute phải nhiều hơn :value ký tự.',
    ],
    'gte'                  => [
        'array'   => 'Mảng :attribute phải có ít nhất :value phần tử.',
        'file'    => 'Dung lượng tập tin :attribute phải lớn hơn hoặc bằng :value KB.',
        'numeric' => 'Giá trị :attribute phải lớn hơn hoặc bằng :value.',
        'string'  => 'Độ dài :attribute phải lớn hơn hoặc bằng :value ký tự.',
    ],
    'hex_color'            => 'Trường :attribute phải là mã màu hexadecimal hợp lệ.',
    'image'                => 'Trường :attribute phải là định dạng hình ảnh.',
    'in'                   => 'Giá trị đã chọn trong trường :attribute không hợp lệ.',
    'in_array'             => 'Trường :attribute phải thuộc tập cho phép trong :other.',
    'integer'              => 'Trường :attribute phải là một số nguyên.',
    'ip'                   => 'Trường :attribute phải là một địa chỉ IP hợp lệ.',
    'ipv4'                 => 'Trường :attribute phải là một địa chỉ IPv4 hợp lệ.',
    'ipv6'                 => 'Trường :attribute phải là một địa chỉ IPv6 hợp lệ.',
    'json'                 => 'Trường :attribute phải là một chuỗi JSON hợp lệ.',
    'list'                 => 'Trường :attribute phải là một danh sách.',
    'lowercase'            => 'Trường :attribute phải là chữ thường.',
    'lt'                   => [
        'array'   => 'Mảng :attribute phải có ít hơn :value phần tử.',
        'file'    => 'Dung lượng tập tin :attribute phải nhỏ hơn :value KB.',
        'numeric' => 'Giá trị :attribute phải nhỏ hơn :value.',
        'string'  => 'Độ dài :attribute phải ít hơn :value ký tự.',
    ],
    'lte'                  => [
        'array'   => 'Mảng :attribute không được có nhiều hơn :value phần tử.',
        'file'    => 'Dung lượng tập tin :attribute phải nhỏ hơn hoặc bằng :value KB.',
        'numeric' => 'Giá trị :attribute phải nhỏ hơn hoặc bằng :value.',
        'string'  => 'Độ dài :attribute phải nhỏ hơn hoặc bằng :value ký tự.',
    ],
    'mac_address'          => 'Trường :attribute phải là một địa chỉ MAC hợp lệ.',
    'max'                  => [
        'array'   => 'Trường :attribute không được vượt quá :max phần tử.',
        'file'    => 'Dung lượng tập tin :attribute không được vượt quá :max KB.',
        'numeric' => 'Trường :attribute không được lớn hơn :max.',
        'string'  => 'Trường :attribute không được vượt quá :max ký tự.',
    ],
    'max_digits'           => 'Trường :attribute không được có nhiều hơn :max chữ số.',
    'mimes'                => 'Trường :attribute phải là một tệp có định dạng: :values.',
    'mimetypes'            => 'Trường :attribute phải là một tệp có định dạng: :values.',
    'min'                  => [
        'array'   => 'Trường :attribute phải có ít nhất :min phần tử.',
        'file'    => 'Dung lượng tập tin :attribute tối thiểu phải là :min KB.',
        'numeric' => 'Trường :attribute phải tối thiểu là :min.',
        'string'  => 'Trường :attribute phải có ít nhất :min ký tự.',
    ],
    'min_digits'           => 'Trường :attribute phải có ít nhất :min chữ số.',
    'missing'              => 'Trường :attribute phải không tồn tại.',
    'missing_if'           => 'Trường :attribute phải không tồn tại khi :other là :value.',
    'missing_unless'       => 'Trường :attribute phải không tồn tại trừ khi :other là :value.',
    'missing_with'         => 'Trường :attribute phải không tồn tại khi có :values.',
    'missing_with_all'     => 'Trường :attribute phải không tồn tại khi có tất cả :values.',
    'multiple_of'          => 'Trường :attribute phải là bội số của :value.',
    'not_in'               => 'Giá trị đã chọn trong trường :attribute không hợp lệ.',
    'not_regex'            => 'Định dạng trường :attribute không hợp lệ.',
    'numeric'              => 'Trường :attribute phải là một số.',
    'password'             => [
        'letters'       => 'Trường :attribute phải chứa ít nhất một chữ cái.',
        'mixed'         => 'Trường :attribute phải chứa ít nhất một chữ hoa và một chữ thường.',
        'numbers'       => 'Trường :attribute phải chứa ít nhất một chữ số.',
        'symbols'       => 'Trường :attribute phải chứa ít nhất một ký tự đặc biệt.',
        'uncompromised' => 'Trường :attribute đã xuất hiện trong một vụ rò rỉ dữ liệu. Vui lòng chọn :attribute khác.',
    ],
    'present'              => 'Trường :attribute phải được cung cấp.',
    'present_if'           => 'Trường :attribute phải được cung cấp khi :other là :value.',
    'present_unless'       => 'Trường :attribute phải được cung cấp trừ khi :other là :value.',
    'present_with'         => 'Trường :attribute phải được cung cấp khi có :values.',
    'present_with_all'     => 'Trường :attribute phải được cung cấp khi có tất cả :values.',
    'prohibited'           => 'Trường :attribute bị cấm.',
    'prohibited_if'        => 'Trường :attribute bị cấm khi :other là :value.',
    'prohibited_unless'    => 'Trường :attribute bị cấm trừ khi :other nằm trong :values.',
    'prohibits'            => 'Trường :attribute cấm :other xuất hiện.',
    'regex'                => 'Định dạng trường :attribute không hợp lệ.',
    'required'             => 'Vui lòng nhập :attribute.',
    'required_array_keys'  => 'Trường :attribute phải chứa các mục cho: :values.',
    'required_if'          => 'Trường :attribute là bắt buộc khi :other là :value.',
    'required_if_accepted' => 'Trường :attribute là bắt buộc khi :other được chấp nhận.',
    'required_if_declined' => 'Trường :attribute là bắt buộc khi :other bị từ chối.',
    'required_unless'      => 'Trường :attribute là bắt buộc trừ khi :other nằm trong :values.',
    'required_with'        => 'Trường :attribute là bắt buộc khi :values được cung cấp.',
    'required_with_all'    => 'Trường :attribute là bắt buộc khi tất cả :values được cung cấp.',
    'required_without'     => 'Trường :attribute là bắt buộc khi :values không được cung cấp.',
    'required_without_all' => 'Trường :attribute là bắt buộc khi không có giá trị nào trong :values được cung cấp.',
    'same'                 => 'Trường :attribute và :other phải giống nhau.',
    'size'                 => [
        'array'   => 'Trường :attribute phải chứa :size phần tử.',
        'file'    => 'Dung lượng tập tin :attribute phải là :size KB.',
        'numeric' => 'Trường :attribute phải bằng :size.',
        'string'  => 'Trường :attribute phải chứa :size ký tự.',
    ],
    'starts_with'          => 'Trường :attribute phải bắt đầu bằng một trong những giá trị sau: :values.',
    'string'               => 'Trường :attribute phải là một chuỗi ký tự.',
    'timezone'             => 'Trường :attribute phải là một múi giờ hợp lệ.',
    'unique'               => ':attribute đã được sử dụng. Vui lòng chọn giá trị khác.',
    'uploaded'             => 'Tải lên trường :attribute thất bại.',
    'uppercase'            => 'Trường :attribute phải là chữ in hoa.',
    'url'                  => 'Trường :attribute phải là một đường dẫn URL hợp lệ.',
    'ulid'                 => 'Trường :attribute phải là một ULID hợp lệ.',
    'uuid'                 => 'Trường :attribute phải là một chuỗi UUID hợp lệ.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        // User / Auth
        'name'                  => 'họ tên',
        'username'              => 'tên đăng nhập',
        'email'                 => 'email',
        'password'              => 'mật khẩu',
        'password_confirmation' => 'xác nhận mật khẩu',
        'current_password'      => 'mật khẩu hiện tại',
        'new_password'          => 'mật khẩu mới',
        'phone'                 => 'số điện thoại',
        'role_id'               => 'vai trò',
        'role'                  => 'vai trò',
        'status'                => 'trạng thái',
        'avatar'                => 'ảnh đại diện',
        'remember'              => 'ghi nhớ đăng nhập',

        // Movie
        'title'                 => 'tiêu đề',
        'description'           => 'mô tả',
        'short_description'     => 'mô tả ngắn',
        'director'              => 'đạo diễn',
        'cast'                  => 'diễn viên',
        'duration'              => 'thời lượng',
        'release_date'          => 'ngày phát hành',
        'poster_url'            => 'ảnh poster',
        'trailer_url'           => 'link trailer',
        'age_rating'            => 'độ tuổi quy định',
        'category_id'           => 'danh mục',
        'category_ids'          => 'danh mục phim',
        'categories'            => 'danh mục',

        // Cinema & Room
        'cinema_id'             => 'rạp chiếu phim',
        'room_id'               => 'phòng chiếu',
        'cinema_name'           => 'tên rạp',
        'room_name'             => 'tên phòng',
        'address'               => 'địa chỉ',
        'city'                  => 'thành phố',
        'capacity'              => 'sức chứa',
        'total_seats'           => 'tổng số ghế',
        'format'                => 'định dạng chiếu',

        // Showtime & Pricing
        'showtime_id'           => 'suất chiếu',
        'start_time'            => 'thời gian bắt đầu',
        'end_time'              => 'thời gian kết thúc',
        'show_date'             => 'ngày chiếu',
        'start_date'            => 'thời gian bắt đầu',
        'end_date'              => 'thời gian kết thúc',
        'surcharge'             => 'phụ phí',
        'price'                 => 'giá vé',
        'ticket_prices'         => 'giá vé theo loại ghế',

        // Seat & Booking
        'seat_id'               => 'ghế',
        'seat_ids'              => 'danh sách ghế',
        'selected_seats'        => 'ghế đã chọn',
        'booking_id'            => 'đơn hàng',
        'booking_code'          => 'mã đặt vé',
        'payment_method'        => 'phương thức thanh toán',
        'total_price'           => 'tổng tiền',
        'discount_amount'       => 'số tiền giảm giá',
        'final_amount'          => 'tổng thanh toán',
        'customer_name'         => 'tên khách hàng',
        'customer_email'        => 'email khách hàng',
        'customer_phone'        => 'số điện thoại khách hàng',
        'note'                  => 'ghi chú',
        'notes'                 => 'ghi chú',

        // Coupon & Combos
        'code'                  => 'mã giảm giá',
        'coupon_code'           => 'mã khuyến mãi',
        'discount_type'         => 'loại giảm giá',
        'discount_value'        => 'giá trị giảm giá',
        'value'                 => 'giá trị',
        'max_discount'          => 'mức giảm tối đa',
        'min_order_value'       => 'giá trị đơn tối thiểu',
        'usage_limit'           => 'giới hạn lượt dùng',
        'quantity'              => 'số lượng',
        'combos'                => 'combo bắp nước',
        'combo_id'              => 'combo',
        'items'                 => 'danh sách mặt hàng',

        // Review & Post & Chat
        'rating'                => 'đánh giá sao',
        'comment'               => 'nội dung bình luận',
        'content'               => 'nội dung',
        'slug'                  => 'đường dẫn',
        'thumbnail'             => 'ảnh đại diện',
        'image'                 => 'hình ảnh',
        'message'               => 'tin nhắn',
        'query'                 => 'từ khóa tìm kiếm',
        'search'                => 'tìm kiếm',
    ],

];
