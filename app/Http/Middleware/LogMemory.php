<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Support\Facades\Log;

class LogMemory{
    
    public function handle($request, Closure $next)
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000, 2);
        $memory = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        Log::info(sprintf(
            "[Perf] %s %s → %sms | %sMB",
            $request->method(),
            $request->path(),
            $duration,
            $memory
        ));

        return $response;
    }
}