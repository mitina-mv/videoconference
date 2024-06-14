<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Отчет</title>
    <style>
        body {
            padding: 0;
            margin: 0;
        }

        .d-grid.grid-col-2.mb-3.gap-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1em;
        }

        p {
            margin: .2em 0;
        }

        table {
            border-collapse: collapse;
        }

        tr, th, td {
            border: 1px solid #000;
            padding: .2em .5em
        }
    </style>
</head>
<body>
    <div class="d-grid gap-4 content">
        <div class="content__container report report_assignment">
            @if($error)
                <div>{{ $error }}</div>
            @endif

            @if($inctuleHeader)
                <h2>Результаты конференции: {{ $vc['name'] ?? 'ошибка' }}</h2>
            @endif

            <div class="d-grid grid-col-2 mb-3 gap-2">
                <div class="assignment-info">
                    <h3>Основные данные</h3>
                    @foreach($vcInfoFields as $field)
                        <p>
                            <b>{{ $field['label'] }}: </b>
                            <span>{{ $field['value'] }}</span>
                        </p>
                    @endforeach
                </div>
                <div class="test-settings">
                    <h3>Настройки тестирования</h3>
                    @if($testInfoFields)
                        @foreach($testInfoFields as $field)
                            <p>
                                <b>{{ $field['label'] }}: </b>
                                <span>{{ $field['value'] }}</span>
                            </p>
                        @endforeach
                    @else
                        <div>Тестирование не проводилось</div>
                    @endif
                </div>
            </div>

            <h3>Сводная таблица</h3>
            <table class="mt-3" style="width: fit-content">
                <thead>
                    <tr>
                        <th>ФИО студента</th>
                        <th>Оценка</th>
                        <th>Коэф. вовлеченности</th>
                        @if($vc['count_check'])
                            <th>Кол-во пройд. ПП</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $g_name => $group)
                        <tr>
                            <th colspan="{{ $vc['count_check'] ? 4 : 3 }}" style="text-align: left; background-color: #ebeced">
                                {{ $g_name }}
                            </th>
                        </tr>
                        @foreach($group as $student)
                            <tr>
                                <td width="300px">
                                    @if($includeHrefDetail && $student['testlog_id'])
                                        <a href="{{ route('report.detail', ['testlog_id' => $student['testlog_id']]) }}" style="text-decoration: underline;">
                                            {{ $student['full_name'] }}
                                        </a>
                                    @else
                                        {{ $student['full_name'] }}
                                    @endif
                                </td>
                                <td width="100">{{ $student['mark'] }}</td>
                                <td>{{ $student['engagement'] }}</td>
                                @if($vc['count_check'])
                                    <td width="150">{{ $student['count_check'] }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>

            @if($comments && $includeComments)
                <h3 class="mt-3 mb-2">Комментарии</h3>
                @foreach($comments as $comment)
                    <p>{{ $comment }}</p>
                @endforeach
            @endif
        </div>
    </div>
</body>
</html>
