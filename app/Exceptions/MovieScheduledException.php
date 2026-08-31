<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MovieScheduledException extends Exception
{
    protected $message = 'Movie is currently scheduled and not yet open for ticket sales.';
    protected $code = 422;

    public function __construct(string $message = 'Movie is currently scheduled and not yet open for ticket sales.', int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], $this->getCode() ?: 422);
    }
}
