# Deployment (AWS)

This app runs against three managed AWS services in production: **RDS for MySQL** (database), **S3** (rendered PNG snapshots of pixel-art sprites), and **Elastic Beanstalk** (hosting, running the app's own Docker image). All of it is optional for local development — with no AWS environment variables set, the app runs exactly as before, sprites just render from the client-side pixel grid instead of a stored PNG.

## 1. S3 bucket (sprite storage)

1. Create a bucket (e.g. `aquarium-manager-sprites`). Keep "Block all public access" **on** at the bucket level.
2. Add a bucket policy that allows public `s3:GetObject` only under the `sprites/*` prefix:
   ```json
   {
     "Version": "2012-10-17",
     "Statement": [{
       "Sid": "PublicReadSprites",
       "Effect": "Allow",
       "Principal": "*",
       "Action": "s3:GetObject",
       "Resource": "arn:aws:s3:::aquarium-manager-sprites/sprites/*"
     }]
   }
   ```
3. Note the bucket name and region — you'll set these as `S3_BUCKET` / `AWS_REGION`.

## 2. RDS for MySQL (database)

1. Create a MySQL 8.0 instance (the free-tier `db.t3.micro` is enough for this app).
2. Set its security group to only allow inbound port 3306 from the Elastic Beanstalk environment's security group (create the EB environment first, or tighten this after step 4).
3. Note the endpoint hostname and port — these become `DB_HOST` / `DB_PORT`.
4. Connect once with a MySQL client (e.g. DBeaver, same as local dev) and run [`db/FishSchema.sql`](db/FishSchema.sql) against it to create the tables.
5. If you already ran an earlier version of this schema locally and are migrating that same database, also run:
   ```sql
   ALTER TABLE PixelArt ADD COLUMN image_url VARCHAR(500) NULL;
   ```
6. Optional TLS: download the [RDS CA bundle](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/UsingWithRDS.SSL.html), deploy it alongside the app, and set `DB_SSL_CA` to its path. Leave `DB_SSL_CA` unset to connect without enforcing TLS.

## 3. IAM role for the EB environment

Create an IAM role for EC2 (the EB instance profile) with an inline policy scoped to just the sprites prefix:

```json
{
  "Version": "2012-10-17",
  "Statement": [{
    "Effect": "Allow",
    "Action": ["s3:PutObject", "s3:GetObject"],
    "Resource": "arn:aws:s3:::aquarium-manager-sprites/sprites/*"
  }]
}
```

Attaching this role to the EB environment's instances lets the AWS SDK pick up credentials automatically (via its default provider chain) — no access keys are stored in the app or its environment variables.

## 4. Elastic Beanstalk

1. Install the EB CLI, then from the repo root: `eb init` and choose the **Docker** platform (not the native PHP platform) — EB will build and run the repo's own `Dockerfile` rather than installing PHP itself.
2. `eb create <environment-name>`, attaching the IAM instance profile from step 3.
3. Set environment properties (Configuration → Software → Environment properties):

   | Key | Value |
   |---|---|
   | `DB_HOST` | RDS endpoint |
   | `DB_PORT` | `3306` |
   | `DB_NAME` | your schema name |
   | `DB_USER` | your DB user |
   | `DB_PASS` | your DB password |
   | `DB_SSL_CA` | path to CA bundle, or omit |
   | `AWS_REGION` | e.g. `us-east-1` |
   | `S3_BUCKET` | e.g. `aquarium-manager-sprites` |

4. `eb deploy`. EB builds the `Dockerfile` (which already runs `composer install` at build time) and runs the resulting container.
5. Verify: `https://<your-eb-url>/health.php` should return `OK`; the login page should load at the root URL.

Once you have the URL, drop it into the "Live demo" line in [README.md](README.md).