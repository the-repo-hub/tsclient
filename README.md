# tsclient

`tsclient` — это PHP-модуль, предназначенный для использования в составе ТВ-приставки на базе Orange Pi. Модуль реализует клиентскую часть взаимодействия с серверной системой **TorrServe**, получая потоковые данные.

## 📌 Назначение

Модуль используется как интерфейс между медиа-системой приставки и сервером **TorrServe**, отвечающим за обработку и трансляцию торрент-контента.

## ⚙️ Технологии

- **Язык:** PHP
- **Платформа:** Orange Pi (Linux)
- **Взаимодействие:** HTTP-запросы, JSON-ответы
- **Сервер:** TorrServe (локальный или удалённый)


## 🧰 Установка и запуск

1. **Настройка FTP-сервера**

   Откройте файл `Makefile` и замените значение переменной `FTP` на IP-адрес вашей собственной OrangePI машины.  
   Пример:
   ```make
   FTP=ftp://192.168.1.10  # замените на ваш адрес
     ```
   затем выполните
   ```
   make deploy
   ```
   и модуль будет установлен на вашем OrangePi
