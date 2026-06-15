# Deploiement

Ce projet peut etre deploye avec Docker sur une plateforme comme Railway, Fly.io, Render avec une base MySQL externe, ou un VPS.

## Variables d'environnement

Configure ces variables sur la plateforme de deploiement :

```env
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=une_valeur_longue_et_aleatoire
DATABASE_URL=mysql://USER:PASSWORD@HOST:PORT/DB_NAME?serverVersion=8.0.32&charset=utf8mb4
MAILER_DSN=null://null
JWT_PASSPHRASE=une_valeur_longue_et_aleatoire
RUN_MIGRATIONS=1
```

Pour JWT, deux options sont possibles :

1. Fournir les contenus des cles :

```env
JWT_PRIVATE_KEY="-----BEGIN ENCRYPTED PRIVATE KEY-----..."
JWT_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----..."
```

2. Fournir des chemins vers des fichiers deja presents dans le conteneur :

```env
JWT_SECRET_KEY=/var/www/html/config/jwt/private.pem
JWT_PUBLIC_KEY=/var/www/html/config/jwt/public.pem
```

La premiere option est recommandee si le depot Git ne contient pas les cles JWT.

## Test local en mode production

```bash
docker compose -f compose.prod.yaml up --build
```

Puis ouvre :

```text
http://localhost:8080
```

## Deploiement Railway

1. Pousse le projet sur GitHub.
2. Cree un nouveau projet Railway depuis le depot GitHub.
3. Ajoute un service MySQL.
4. Configure les variables d'environnement ci-dessus.
5. Dans le service web, utilise `Dockerfile.prod` comme Dockerfile.
6. Deploy.

Railway fournit automatiquement une variable `PORT`. Le script de demarrage Apache l'utilise automatiquement.

Pour `DATABASE_URL`, tu peux construire l'URL avec les variables du service MySQL Railway :

```env
DATABASE_URL=mysql://${MYSQLUSER}:${MYSQLPASSWORD}@${MYSQLHOST}:${MYSQLPORT}/${MYSQLDATABASE}?serverVersion=8.0.32&charset=utf8mb4
```

## Deploiement Render

Render peut deployer le `Dockerfile.prod`, mais il fournit surtout PostgreSQL comme base geree. Pour garder MySQL sans changer le code, utilise une base MySQL externe ou choisis Railway.

## Commandes utiles apres deploiement

Lancer les migrations manuellement si `RUN_MIGRATIONS` n'est pas active :

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

Charger les fixtures seulement pour une demo :

```bash
php bin/console doctrine:fixtures:load --no-interaction
```
