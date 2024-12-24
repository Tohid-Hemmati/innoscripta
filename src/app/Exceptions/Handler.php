<?php

namespace App\Exceptions;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler implements ExceptionHandler
{
    public function report(Throwable $e)
    {
        if ($this->shouldReport($e)) {
            Log::error($e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    public function render($request, Throwable $e)
    {
        $status = $this->getHttpStatus($e);

        if (config('app.debug')) {
            return response()->json([
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ], $status);
        }

        return response()->json([
            'message' => 'An error occurred. Please try again later.',
        ], $status);
    }

    public function renderForConsole($output, Throwable $e)
    {
        $output->writeln('<error>' . $e->getMessage() . '</error>');
        if (config('app.debug')) {
            $output->writeln($e->getTraceAsString());
        }
    }

    public function shouldReport(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return false;
        }

        return true;
    }

    protected function getHttpStatus(Throwable $e): int
    {
        return method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
    }
}
