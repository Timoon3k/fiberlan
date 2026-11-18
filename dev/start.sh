#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
umask 077
mkdir -p .local
if [[ ! -f .local/compose.env ]]; then
  {
    printf 'DATABASE_PASSWORD=%s\n' "$(openssl rand -hex 32)"
    printf 'DATABASE_ROOT_PASSWORD=%s\n' "$(openssl rand -hex 32)"
  } > .local/compose.env.tmp
  mv .local/compose.env.tmp .local/compose.env
fi
if [[ ! -f .local/admin-password ]]; then
  openssl rand -hex 32 > .local/admin-password
fi

# Apache reads the public theme through a read-only bind mount.
chmod -R a+rX theme/fiberlan plugin/fiberlan-site
chmod a+r dev/initialize.php dev/mail.php
compose=(docker compose --env-file .local/compose.env)
"${compose[@]}" up -d --wait
ready=0
for ((attempt=0; attempt<90; attempt++)); do
  if curl --noproxy '*' -fsS --max-time 3 --output /dev/null http://127.0.0.1:8080/wp-login.php; then
    ready=1
    break
  fi
  sleep 1
done
if [[ "$ready" != 1 ]]; then
  echo 'WordPress did not answer on port 8080.' >&2
  exit 1
fi
"${compose[@]}" exec -T --user www-data wordpress php /opt/fiberlan-init.php install < .local/admin-password
"${compose[@]}" exec -T --user www-data wordpress php /opt/fiberlan-init.php
curl --noproxy '*' -fsSL --max-time 20 --output /dev/null http://127.0.0.1:8080/
