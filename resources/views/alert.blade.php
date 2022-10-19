<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <style>

        #alerts {
            border-collapse: collapse;
            width: 100%;
        }

        #alerts td, #alerts th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        #alerts tr:nth-child(even){background-color: #f2f2f2;}

        #alerts thead {
            padding-top: 12px;
            padding-bottom: 12px;
            text-align: left;
            background-color: #0452d1;
            color: white;
        }

        #alerts .Critical {
            color: #d10303;
        }

        #alerts .Medium {
            color: #d19302;
        }
    </style>
</head>
<body>
    <table id="alerts">
        <thead>
            <td>Name</td>
            <td>Type</td>
            <td>Level</td>
            <td width="40%">Content</td>
            <td>Started At</td>
        </thead>
        <tbody>
            @foreach($alertEvents as $alertEvent)
                <tr>
                    <td>{{$alertEvent->name}}</td>
                    <td>{{$alertEvent->type}}</td>
                    <td class="{{$alertEvent->level}}">{{$alertEvent->level}}</td>
                    <td>{{$alertEvent->content}}</td>
                    <td>{{\Carbon\Carbon::createFromTimeString($alertEvent->created_at, 'UTC')->setTimezone('America/New_York')->toDateTimeString()}}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>