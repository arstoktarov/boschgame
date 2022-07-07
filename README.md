<p align="center"><img src="https://res.cloudinary.com/dtfbvvkyp/image/upload/v1566331377/laravel-logolockup-cmyk-red.svg" width="400"></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License"></a>
</p>

## О проекте

Мобильная игра-викторина образовательного направления.

## Функционал проекта

Функционал проекта состоит из интерфейса интеллектуальной битвы двух человек, в точности копирующей идею игры Борьба Умов(Quizduell).

## Использованные технологии

Проект является Back-end частью игры, и в запуске не нуждается. Общение с front частью происходит через RESTApi, и частично wesocket-ы(обновление статуса игр, и статуса игры) реализованный с помощью nodejs/redis

## Дополнительные комментарии

Проект в запуске не нуждается, т.к. без front-end части это будет бессмысленно. Скорее существует для ознакомления с кодом и его реализацией. Websocket часть очень маленькая, это просто Redis pub/sub который связывается с nodejs, который в свою очередь отправляет эти сообщение через websocket. Redis c Laravel общается через встроенную в Laravel Event/Listener, Broadcast систему используя библиотеку predis.
