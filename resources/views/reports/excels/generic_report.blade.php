<?php /** @var \Sheba\Reports\Report $report */ ?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $report->getTitle() }}</title>
</head>
<body>
<table>
    <tr>
        @foreach($headers = $report->getHeaders() as $header)
            <th>{{ isNormalized($header) ? $header : normalizeStringCases($header) }}</th>
        @endforeach
    </tr>

    @foreach($report->getRows() as $row)
        <tr>
            @foreach($headers as $header)
                <td>{{ $row[$header] }}</td>
            @endforeach
        </tr>
    @endforeach
</table>
</body>
</html>
