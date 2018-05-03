<!doctype html>
<html lang="en">
    <head>
        <title>{{ Config::get('build-metadata.appName') }} :: Build Metadata</title>
    </head>
    <body>
        <div>
            <code>
                <strong>Project Name:</strong> {{ $projectName }}<br/>
                <strong>Project Version:</strong> {{ $projectVersion }}<br/>
                <strong>VCS Branch:</strong> {{ $vcsBranch }}<br/>
                <strong>VCS Revision:</strong> {{ $vcsRevision }}<br/>
                <strong>Build Number:</strong> {{ $buildNumber }}<br/>
                <strong>Build Timestamp:</strong> {{ $buildTimestamp }}
            </code>
        </div>
    </body>
</html>


