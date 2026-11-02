<?php
/**
 * Ngoại lệ Nghiệp vụ (BusinessException)
 * Giúp tầng Service ném lỗi có kiểm soát (HTTP 400, 401, 403, 404) mà không phụ thuộc hàm exit/echo của Controller.
 */
class BusinessException extends Exception {
    private int $statusCode;

    public function __construct(string $message, int $statusCode = 400) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
    }

    public function getStatusCode(): int {
        return $this->statusCode;
    }
}
?>

