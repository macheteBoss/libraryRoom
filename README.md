# libraryRoom

Инструкция по развёртыванию:

Клонируем репо-рий:

# git clone https://github.com/macheteBoss/libraryRoom.git

Заходим в папку с проектом, открываем в терминале, выполняем:

# docker-compose up --build -d

Заходим в контейнер:
# docker exec -it library1-php-cli bash

Выполняем команды:
(Для генерации тестовых данных были использованы фикстуры. Они не идут в стандартном наборе, пришлось подтягивать. Вроде как доп.бандлы нельзя было использовать, но я подумал ничего страшного)

# composer install

# php bin/console doctrine:migrations:migrate

# php bin/console doctrine:fixtures:load


Переходим на localhost:8088
