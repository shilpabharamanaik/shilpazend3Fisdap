<!doctype html>
<html lang="en">
    <head>
        <title>{{ Config::get('health-checks.appName') }} :: Health Check :: Status</title>
    </head>
    <body>
        <div>
            @if (count($completedChecks) > 0)
            <code>
                @foreach ($completedChecks as $check)
                    <strong>{{$check->getName()}} status:</strong>
                    @if ($check->getStatus() != 'OK')
                    <span style="color:#C00;">
                    @else
                    <span style="color:#090;">
                    @endif
                    {{ $check->getStatus() }}
                        @if ($check->getError() !== null)
                            - {{ $check->getError() }}
                        @endif
                    ({{ $check->getRunTime() }} ms)
                    </span><br/><br/>
                @endforeach
            </code>
            <p>
                <code>
                    Total time for all checks: {{ $totalRunTime }} ms
                </code>
            </p>
            @endif

            @if ($brokenHealthCheckCount > 0)
            <code><span style="color:#C00;">Some health checks failed to run.  Please check the logs.</span></code>
            @endif
        </div>
    </body>
</html>


