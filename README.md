Проект развернут на сайте employees.andreiikachanov.com

Для выполнения использовался фреймворк Symfony 3.4.9

1) На главной странице выводится иерархия сотрудников в древовидной форме c помощью библиотеки JsTee. Данные загружаются при открытии каждого уровня с помощью ленивой загрузки(lazy load).

2) Базовые стили созданы с помощью css фреймворка Bootstrap 4.

2) На странице <a href="">Employees</a> выводится общий список всех сотрудников.

3) Добавлена возможность сортировать сотрудников по любому полю без перезагрузки страницы, с помощью библиотеки jquery.tablesorter.

4) Добавлена возможность искать сотрудников по всем критериям без перезагрузки страницы. Данные обновляются с помощьют jQuery Ajax.

5) С помощью стандартных механизмов Symfony добавлена аутентификация пользователя для закрытого раздела (логин - admin, пароль - qwerty).

6) Добавлена возможность создания нового, редактирования и удаления сотрудника. При удалении сотрудника, начальником всех его подчиненных становится начальник удаленного сотрудника. Для наглядности, перед удалением такого сотрудника открывается модальное окно со списком всех его подчиненных и с сотрудником, на котого они будут переподчинены.

7) Добавлена возможность добавлять и редактировать фотографию сотрудника(jpg, jpeg, png)
шириной и высотой не больше 500px.

8) База данных создана с помощью миграций и заполнена с помощью DoctrineFixtures.

9) Javascript/css библиотеки добавлены с помощью bower, php библиотеки с помощью composer.

Возможность менять начальника сотрудника используя drag-n-drop не реализовал, в связи с нехваткой опыта.