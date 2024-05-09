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
        theme_id: {
            title: "Тема",
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
        }
    },
};
