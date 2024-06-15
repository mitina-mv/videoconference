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
@section('title', 'Результаты тестирования: ' . ($test['name'] ?? 'ошибка'))

@section('content')
    <div class="content">
        <div class="content__container report report_assignment">
            <h2 v-if="$inctuleHeader">Результаты тестирования: {{ $test['name'] }}</h2>
            <div class="d-grid grid-col-2 mb-3 gap-2">
                <div class="assignment-info">
                    <h3>Основные данные</h3>
                    @foreach ($testInfoFields as $field)
                        <p>
                            <b>{{ $field['label'] }}:</b>
                            <span>{{ $field['value'] }}</span>
                        </p>
                    @endforeach
                </div>
                <div class="test-settings">
                    <h3>Настройки тестирования</h3>
                    @foreach ($settingsFields as $field)
                        <p>
                            <b>{{ $field['label'] }}:</b>
                            <span>{{ $field['value'] }}</span>
                        </p>
                    @endforeach
                </div>
            </div>
            <h3>Сводная таблица</h3>
            <table class="mt-3" style="width: fit-content;">
                <thead>
                    <tr>
                        <th>ФИО студента</th>
                        <th>Оценка</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($groups as $g_name => $group)
                        <tr>
                            <th colspan="2" style="text-align: left; background-color: #ebeced">{{ $g_name }}</th>
                        </tr>
                        @foreach ($group as $student)
                            <tr>
                                <td width="300px">{{ $student['full_name'] }}</td>
                                <td width="100">{{ $student['mark'] }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
</body>
</html>