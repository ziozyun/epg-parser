#!/bin/bash

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ENV_FILE="$SCRIPT_DIR/../.env"

if [ ! -f "$ENV_FILE" ]; then
  echo "Файл $ENV_FILE не знайдено!"
  exit 1
fi

set -a
. "$ENV_FILE"
set +a

{
  printf 'export DB_HOST=host.docker.internal\n'
  printf 'export DB_PORT=%q\n' "$DB_PORT"
  printf 'export DB_NAME=%q\n' "$DB_NAME"
  printf 'export DB_USER=%q\n' "$DB_USER"
  printf 'export DB_PASSWORD=%q\n' "$DB_PASSWORD"
  printf 'export EPG_BATCH_SIZE=%q\n' "$EPG_BATCH_SIZE"
  printf 'export EPG_IN_CONTAINER=1\n'
} >> ~/.bashrc
