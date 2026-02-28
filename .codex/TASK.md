Ты — senior full-stack разработчик. Разработай монолитный проект:

Доп. требования к результату (обязательно):

A) Перед кодом выведи “План действий”:
- Этапы (1..N) с чеклистом
- Что создаём в БД / бэкенде / фронте
- Порядок реализации (миграции -> модели -> API -> фронт -> отчёты)
- Риски/edge-cases (конфликты записей, списание пакета, отмены)

B) Структура и нейминг должны быть аналогичны проекту-референсу:
D:\project\call-grade-panel

Правила:
- Используй те же конвенции именования, что в call-grade-panel:
    * нейминг папок/файлов
    * нейминг роутов
    * нейминг контроллеров/сервисов
    * нейминг DTO/Request/Resource
    * стиль Vue компонентов и структура страниц
- Размести админку во фронте строго в resources/js/admin (как ранее).
- Используй такие же базовые слои и паттерны, как в call-grade-panel (например: Services, Repositories, DTO, Resources — если они там есть).
- Если в call-grade-panel есть единый ApiClient/axios wrapper, middleware, base layout, router-structure — повтори.

ВАЖНО: если структура call-grade-panel не предоставлена, то:
1) Сначала опиши “Предполагаемую структуру” (как ты её сделаешь по best practices Laravel+Vue3+Bootstrap5),
2) И добавь блок “Что нужно скопировать из call-grade-panel для 100% совпадения” (список: tree проекта, composer.json, package.json, vite config, router, layout, API client).

Backend:
- Laravel 10+ (PHP 8.2+)
- REST API в том же проекте
- БД: MySQL 8
- Деньги: EUR (DECIMAL(10,2))

Frontend (админка):
- Vue 3 (Composition API)
- Bootstrap 5 (последняя версия)
- Никаких других UI-фреймворков
- Сборка через Vite
- Весь фронт админки должен находиться в папке:
  resources/js/admin
- Использовать Bootstrap layout: container, row, col, cards, tables, forms, modals
- Иконки: Bootstrap Icons
- Адаптивная верстка

Структура frontend:
resources/js/admin/
├── app.js
├── router.js
├── components/
├── layouts/
├── pages/
│    ├── ClientsList.vue
│    ├── ClientShow.vue
│    ├── VisitForm.vue
│    ├── CalendarView.vue
│    ├── Packages.vue
│    ├── Devices.vue
│    ├── Stats.vue
│    └── Finance.vue
└── services/api.js (axios wrapper)

Использовать единый AdminLayout.vue:
- Sidebar слева
- Navbar сверху
- Контент справа
- Breadcrumbs
- Flash уведомления

=== ОБЩИЕ ПРАВИЛА ===
- Все суммы в EUR
- Тип денег в БД: DECIMAL(10,2)
- Все даты хранить в UTC
- Soft delete для основных сущностей
- Индексы для поиска
- ENUM статусы через PHP Enum
- Файлы хранить через Laravel Storage (public disk)

=== СУЩНОСТИ ===

1) Clients
- full_name
- phone (индекс)
- birth_date
- status: NEW / ACTIVE / LOST
- notes
- противопоказания (boolean поля)
- last_visit_at
- softDeletes

2) Visits
- client_id
- service_id
- device_id (nullable)
- master_id
- zone
- starts_at
- price (EUR)
- payment_method
- visit_status
- master_comment
- deduct_from_package (boolean)

3) Packages
- templates
- client packages
- remaining_procedures
- expires_at
- package_redemptions

4) Devices
- name
- purchased_at
- cost (EUR)

5) Payments
- client_id
- visit_id (nullable)
- amount (EUR)
- payment_method
- paid_at

6) Expenses
- type
- amount (EUR)
- date
- note

=== ФИНАНСОВАЯ ЛОГИКА ===
- Доход = сумма payments
- Прибыль = доход – расходы
- Доход аппарата = сумма visits.price где device_id и status ARRIVED
- Средний чек = revenue / count
- Баланс клиента считать через ledger или payments – charges

=== КАЛЕНДАРЬ ===
- Вид день/неделя
- Фильтр мастера
- Цвета по услугам
- Быстрое создание визита через modal

=== ОТЧЕТЫ ===
Фильтр:
- период
- мастер
- услуга
- аппарат

Отчеты:
- по услугам
- по мастерам
- по клиентам
- финансы за день

=== ВЫВЕСТИ ===
1) ER-диаграмму (текст)
2) Laravel migrations
3) Eloquent модели
4) API routes
5) Controllers
6) Пример FormRequest
7) Seeder
8) Vue страницы с Bootstrap разметкой
9) Структуру Vite подключения Bootstrap
10) Пример AdminLayout.vue

Выводи код по файлам с путями.
Не пропускай важные части.
