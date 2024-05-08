export default {
    students: {
        title: 'Студенты',
        case: [
            'студент', 'студента',
            'студенту', 'студента', 
            'студентом', 'студенте',
        ],
    },
    teachers: {
        title: 'Преподаватели',
        case: [
            'преподаватель', 'преподавателя',
            'препдавателю', 'преподавателя', 
            'преподавателем','преподавателе'
        ],
    }, 
    questions: {
        title: 'Вопросы пользователя',
        case: [
            'вопрос', 'вопроса',
            'вопросу', 'вопрос', 
            'вопросом','вопросе'
        ],
    }, 
    user_fields: {
        name: {
            title: 'Имя',
        },
        lastname: {
            title: 'Фамилия',
        },
        patronymic: {
            title: 'Отчество',
        },
        full_name: {
            title: 'Полное имя',
        },
        email: {
            title: 'Email',
        },
        role: {
            title: 'Роль',
        },
        studgroup: {
            title: 'Группа',
            placeholder: 'Выберите группу из списка',
        },
        studgroups: {
            title: 'Группы',
            placeholder: 'Выберите группы из списка',
        }
    },
    reference_fields: {
        name: {
            title: 'Название',
        },
        code: {
            title: 'Код',
        }
    },
    page_titles: {
        users: 'Пользователи',
        users: 'Пользователи',
        groups: 'Группы',
        teachers: 'Преподаватели',
        reference_studgroups: 'Список групп',
        reference_disciplines: 'Список дисциплин',
        questions: 'Банк заданий',
    }, 
    questions_fields: {
        text: {
            title: 'Вопрос',
        },
        answers: {
            title: 'Варианты ответов',
        }, 
        is_private: {
            title: 'Приватность',
        },
        mark: {
            title: 'Стоимость',
        },
        type: {
            title: 'Тип вопроса',
            values: {
                single: "Одиночный выбор",
                multiple: "Множественный выбор",
                text: "Текстовый ответ",
            }
        }
    }
};