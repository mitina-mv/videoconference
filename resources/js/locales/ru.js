export default {
    students: {
        title: "Студенты",
        case: [
            "студент",
            "студента",
            "студенту",
            "студента",
            "студентом",
            "студенте",
        ],
    },
    teachers: {
        title: "Преподаватели",
        case: [
            "преподаватель",
            "преподавателя",
            "препдавателю",
            "преподавателя",
            "преподавателем",
            "преподавателе",
        ],
    },
    questions: {
        title: "Вопросы пользователя",
        case: ["вопрос", "вопроса", "вопросу", "вопрос", "вопросом", "вопросе"],
    },
    studgroups: {
        title: "Группа студентов",
        case: ["группа", "группы", "группе", "группу", "группой", "группе"],
    },
    disciplines: {
        title: "Дисциплины",
        case: [
            "дисциплина",
            "дисциплины",
            "дисциплине",
            "дисциплину",
            "дисциплиной",
            "дисциплине",
        ],
    },
    videoconferences: {
        title: "Конференции",
        case: ["конференция",  "конференции",  "конференции",  "конференцию",  "конференцией",  "конференции"],
    },
    tests: {
        title: "Шаблоны тестов",
        case: ["шаблон", "шаблона", "шаблону", "шаблон", "шаблоном", "шаблоне"],
    },
    assignments: {
        title: "Назначение тестов",
        case: ["назначение", "назначения", "назначению", "назначение", "назначением", "назначении"],
    },
    user_fields: {
        name: {
            title: "Имя",
        },
        lastname: {
            title: "Фамилия",
        },
        patronymic: {
            title: "Отчество",
        },
        full_name: {
            title: "Полное имя",
        },
        email: {
            title: "Email",
        },
        role: {
            title: "Роль",
        },
        studgroup: {
            title: "Группа",
            placeholder: "Выберите группу из списка",
        },
        studgroups: {
            title: "Группы",
            placeholder: "Выберите группы из списка",
        },
    },
    reference_fields: {
        name: {
            title: "Название",
        },
        code: {
            title: "Код",
        },
    },
    page_titles: {
        users: "Пользователи",
        users: "Пользователи",
        groups: "Группы",
        teachers: "Преподаватели",
        reference_studgroups: "Список групп",
        reference_disciplines: "Список дисциплин",
        reference_themes: "Список тем занятий",
        questions: "Банк заданий",
        answers: "Варианты ответов",
        tests: "Шаблоны тестов",
        assignments: "Тестирования",
        videoconferences: "Видеоконференции",
        testing: 'Тестирование'
    },
    questions_fields: {
        text: {
            title: "Вопрос",
        },
        answers: {
            title: "Варианты ответов",
        },
        is_private: {
            title: "Приватность",
        },
        mark: {
            title: "Стоимость",
        },
        theme: {
            title: "Тема",
        },
        theme_id: {
            title: "Тема",
        },
        discipline_id: {
            title: "Дисциплина",
        },
        type: {
            title: "Тип вопроса",
            values: [
                { id: "single", name: "Одиночный выбор" },
                { id: "multiple", name: "Множественный выбор" },
                { id: "text", name: "Текстовый ответ" },
            ],
        },
    },
    answers_fields: {
        status: {
            title: "Правильность",
        },
        name: {
            title: "Текст ответа",
        },
    },
    info_messages: {
        answer_error:
            "При отсуствии ответов вопрос не будет активен! Для вопроса типа одиночного выбора необходим только один правильный ответ. Для вопроса типа множественного выбора необходимо указать все правильные ответы. Для вопроса типа текстового ответа все ответы должны быть указаны как правильные.",
        test_fixed_question_warn: "В данном режиме поле \"количество вопросов\" не влияет на итоговый тест. Тестовое задание будет состоять из указанных Вами вопросов.",
        test_fixed_question_error: "По данной теме нет вопросов в базе или Вы не задали необходимые параметры.",
    },
    test_fields: {
        name: {
            title: "Название шаблона",
        },
        description: {
            title: "Описание шаблона",
        },
        theme: {
            title: "Тема",
        },
        theme_id: {
            title: "Тема",
        },
        discipline_id: {
            title: "Дисциплина",
        },
        settings: {
            title: "Настройки",
            values: [
                {
                    id: "count_questions",
                    name: "Количество вопросов",
                    type: "number",
                    default: 10,
                },
                {
                    id: "is_random",
                    name: "Случайный порядок вопросов",
                    type: "bool",
                    default: false,
                },
                {
                    id: "time_limit",
                    name: "Ограничение времени прохождения, (мин)",
                    type: "number",
                    default: null,
                },
                {
                    id: "permission_switch_questions",
                    name: "Разрешено переключать вопрос",
                    type: "bool",
                    default: false,
                },
                {
                    id: "fixed_questions",
                    name: "Предустановленные вопросы",
                    type: "bool",
                    default: false,
                },
            ],
        },
    },
    assignments_fields: {
        date: {
            title: "Дата проведения",
        },
        test_id: {
            title: "Шаблон теста",
        },
        date: {
            title: "Дата проведения",
        },
        themes: {
            title: "Тема",
        },
        studgroups: {
            title: 'Группы студентов',
        },
        mark:  {
            title: "Оценка",
        },
        settings:  {
            title: "Настройки",
        },
        moodle_code: {
            title: "Код мероприятия в Moodle",
        },
    },
    videoconferences_fields:  {
        session: {
            title: "Сессия",
        },
        name: {
            title: "Название",
        },
        studgroups: {
            title: 'Группы студентов',
        },
        date: {
            title: "Дата",
        },
        test:  {
            title: "Шаблон теста",
        },
        settings:  {
            title: "Настройки",
            values: [
                {
                    id: "permission_video",
                    name: "Использование камеры",
                    type: "bool",
                    default: false,
                },
                {
                    id: "permission_audio",
                    name: "Использование аудио",
                    type: "bool",
                    default: false,
                },
                {
                    id: "permission_mute",
                    name: "Функция «заглушить всех»",
                    type: "bool",
                    default: true,
                },
                {
                    id: "type",
                    name: "Тип занятия",
                    type: "dropdown",
                    values: [
                        { id: "lecture", name: "Лекция" },
                        { id: "practice", name: "Практика" },
                    ],
                    default: "lecture",
                },
            ],
        },

    }
};
