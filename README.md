# epg-parser

Це консольний проєкт для імпорту телепрограми (XMLTV) з gzip‑архіву в PostgreSQL
та побудови звіту на основі даних у БД. Парсинг відбувається стрімінгово, тому
підходить для великих файлів без завантаження всього XML у памʼять.

## Швидкий старт

1) Перевірити `.env` (можна взяти за основу `.env.example`):
```
cp .env.example .env
```

2) Запустити контейнери (якщо працюєш ззовні контейнера):
```
make up
```

3) Встановити залежності:
```
make build
```

4) Виконати міграції:
```
make migrate
```

5) Імпортування XML з телепрограмою:
```
make import ARGS="https://epg.prosto.tv/v2/export/xmltv.gz?token=..."
```

6) Звіт:
```
make report ARGS="--lang=uk"
```

## Робота всередині Devcontainer

Якщо ти працюєш всередині devcontainer, команди `make` можна виконувати так само.
Вони самі визначають, чи запускати PHP напряму або через `docker compose exec`.

Приклади:
```
make import ARGS="https://epg.prosto.tv/v2/export/xmltv.gz?token=..."
make report ARGS="--lang=uk"
make format
```

## Імпорт

Мінімальний запуск:
```
make import ARGS="https://epg.prosto.tv/v2/export/xmltv.gz?token=..."
```

З фільтрами каналів і часу:
```
make import ARGS="https://... --channels=1,3,7 --from=20260118000000+0200 --to=20260119000000+0200"
```

Пояснення параметрів:
- `--channels=1,3,7` — список ID каналів
- `--from=YYYYMMDDHHMMSS+ZZZZ` — початок діапазону
- `--to=YYYYMMDDHHMMSS+ZZZZ` — кінець діапазону

Якщо фільтри не задані — обробляються всі канали й усі передачі.

Ідемпотентність: повторний запуск не створює дублікатів, дані оновлюються через upsert.
Записи, яких більше немає у фіді, не видаляються (синхронізація лише на додавання/оновлення).

## Звіт

Звіт читає дані з БД (без повторного парсингу):
```
make report ARGS="--lang=uk"
```

## Форматування коду

```
make format
```

## Керування контейнерами (ззовні контейнера)

```
make up
make down
make clean
```

## Налаштування

Параметри з `.env`:
```
DB_HOST
DB_PORT
DB_NAME
DB_USER
DB_PASSWORD
EPG_BATCH_SIZE
```

`EPG_BATCH_SIZE` задає розмір партії запису (за замовчуванням 500).
