#!/bin/bash

pg_dump  \
  --host=127.0.0.1 \
  --port=5432 \
  --username=web4all \
  --format=plain \
  --file=web4all.sql \
  --create \
  --blobs \
  --no-owner \
  --verbose \
  web4all
