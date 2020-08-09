<?php namespace Tranquility\App\Errors\Helpers;

use Throwable;
use Slim\Interfaces\ErrorRendererInterface;
use Tranquility\System\Enums\HttpStatusCodeEnum;
use Tranquility\System\Utility;

class ErrorRenderer implements ErrorRendererInterface {
    /**
     * @param Throwable $exception
     * @param bool      $displayErrorDetails
     * @return string
     */
    public function __invoke(Throwable $exception, bool $displayErrorDetails): string {
        // Get error code from exception
        $code = $exception->getCode();
        if ($code == 0) {
            $code = HttpStatusCodeEnum::InternalServerError;
        }

        // Generate 'error object' from exception
        $result = [
            'id' => Utility::generateUuid(1),
            'status' => $code,
            'code' => $code,
            'title' => $exception->getMessage()
        ];

        // Add exception details to meta element
        if ($displayErrorDetails == true) {
            $result['meta'] = [];
            $result['meta']['exceptionDetails'] = [];
            do {
                $result['meta']['exceptionDetails'][] = $this->formatExceptionFragment($exception);
            } while ($exception = $exception->getPrevious());
        }

        return (string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * @param Throwable $exception
     * @return array
     */
    private function formatExceptionFragment(Throwable $exception): array
    {
        return [
            'type'    => get_class($exception),
            'code'    => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
            'trace'   => $exception->getTrace()
        ];
    }
}