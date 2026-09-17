# aquarium-manager

A full-stack aquarium tracker: log real tank data (livestock, water tests, maintenance, reminders) and watch it drive an animated pixel-art version of the tank.

**Demo:** http://aquarium-manager-env.eba-ayrpkhs3.us-east-1.elasticbeanstalk.com

## Tech Stack

- **Backend:** PHP 8.4, MySQL via PDO; REST API spanning 12 endpoints across 9 relational tables
- **Frontend:** JavaScript, Chart.js
- **Infrastructure:** Docker, AWS (RDS, S3, Elastic Beanstalk), Composer + AWS SDK for PHP
- **CI:** GitHub Actions
- **External APIs:** iNaturalist (species identification autofill), SerpApi (Google Shopping price autofill)

## Notable Features

- **Hand-drawn pixel art editor**: grid-based drawing tool (paint/erase/flood-fill) for creating each organism's in-app sprite from scratch.
- **Virtual Pixel Tank**: organisms swim (fish, and invertebrates near the substrate) or sway in place (plants, coral) inside a tank shaped to match the real tank (rectangular, bowfront, hex, cube), scaled by growth stage.
- **Compatibility & stocking warnings**: a rules engine checks tank size, temperature/pH range, temperament, and group-size requirements against species data before problems happen.
- **Tank health score**: a single 0–100 score computed from water parameter trends, overdue reminders, and maintenance recency, with a plain-language suggestion.
- **Growth-stage estimation**: auto-suggests Juvenile/Sub-adult/Adult based on current vs. expected adult size, always overridable by the user.
- **Live external data lookups**: species search autofills scientific name, common name, and type from iNaturalist; wishlist items autofill real prices from Google Shopping.

## Local Development

```
docker compose up --build
```
Serves the app at `http://localhost:8080` with a local MySQL container, schema loaded automatically. [DEPLOYMENT.md](DEPLOYMENT.md) for the full cloud deployment walkthrough.
