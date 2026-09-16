# aquarium-manager

A fun, practical aquarium management app that lets users track their real aquariums while maintaining a pixel-art virtual version of each tank. Users can log livestock, plants, water parameters, maintenance tasks, reminders, notes, photos, wishlist items, and tank health trends.

**Live demo:** http://aquarium-manager-env.eba-ayrpkhs3.us-east-1.elasticbeanstalk.com

## Cloud Architecture

Runs on AWS in production:

- **RDS for MySQL** — managed database (swap-in replacement for local MySQL via env vars, no code changes needed).
- **S3** — stores a rendered PNG snapshot of each hand-drawn pixel-art sprite, served directly as thumbnails; the editable grid data itself stays in MySQL.
- **Docker** — the app is packaged as a container (see [Dockerfile](Dockerfile)); the same image runs locally (`docker compose up`) and in production.
- **Elastic Beanstalk** — hosting, running that Docker image, with an instance IAM role scoped to the sprites bucket (no access keys stored anywhere).

None of this is required for local development — `php -S localhost:8080 -t public` still works standalone, and with no AWS environment variables set the app runs entirely locally with sprites rendered from the client-side pixel grid instead of a stored PNG. See [DEPLOYMENT.md](DEPLOYMENT.md) for the full setup checklist.

## Project Summary

**Manage Aquarium** is a personal aquarium journal and management system. Each user can create one or more tanks, add fish, invertebrates, plants, or other aquarium life, and see those additions reflected in a virtual pixel-art tank.

The app should be enjoyable to use, but also useful for real aquarium care. It should help users remember maintenance, track water quality, monitor livestock health, plan future purchases, and understand how their tank changes over time.

## Core Goals

- Help users manage real aquariums in an organized way.
- Create a virtual pixel-art version of each tank.
- Track fish, invertebrates, plants, and other tank inhabitants.
- Store notes, health updates, and maintenance history.
- Record water test results and visualize trends.
- Send reminders for feeding, fertilizing, water changes, and other tasks.
- Support multiple users with authentication.
- Allow users to personalize their tank and livestock with custom pixel art.

## Main Features

### 1. User Accounts

Users should be able to create accounts and manage their own aquarium data.

**Required functionality:**

- Sign up
- Log in
- Log out
- Secure authentication
- User-specific tank data
- Support for multiple users

### 2. Tank Profiles

Each user can create one or more aquarium profiles.

**Tank information may include:**

- Tank name
- Tank type: freshwater, saltwater, brackish, planted, shrimp, etc.
- Dimensions
- Estimated volume
- Start date
- Substrate type
- Filter type
- Heater information
- Lighting schedule
- Fertilizer routine
- CO₂ usage
- Notes

The tank profile should act as the central object that connects livestock, water tests, reminders, maintenance logs, and the virtual tank.

### 3. Livestock and Plant Database

Users can add organisms to a specific tank.

Supported categories:

- Fish
- Invertebrates
- Plants
- Corals, if saltwater support is added later
- Other custom categories

Each added organism should have two types of data:

#### Species Data

General information about the species.

Examples:

- Common name
- Scientific name
- Category
- Adult size
- Temperature range
- pH range
- Minimum tank size
- Diet
- Temperament
- Care level
- Schooling/group requirement
- Plant-safe status
- Shrimp-safe status
- Notes

#### User-Owned Organism Data

Information about the user's specific animal or plant.

Examples:

- Custom name
- Species
- Tank
- Date added
- Current size
- Growth stage
- Health status
- Behavior notes
- Disease notes
- Feeding notes
- Custom pixel art
- Photos
- Active/inactive status

This separation keeps species information reusable while allowing users to personalize their own aquarium inhabitants.

### 4. Virtual Pixel Tank

When a user adds an organism to a tank, a pixel-art version should appear in the virtual tank.

**Features:**

- Fish, plants, and invertebrates appear visually in the tank.
- Organisms can have default pixel-art sprites.
- Users can draw or customize their own pixel art.
- Users can name individual organisms.
- Fish can appear to swim or move around.
- Plants can stay rooted in place.
- Invertebrates can move slowly along the bottom or decorations.
- Organisms can visually grow over time.

The virtual tank should be fun, decorative, and connected to the user's real tank data.

### 5. Custom Pixel Art Editor

Users should be able to personalize their organisms.

**Editor functionality:**

- Open a pixel-art drawing grid.
- Select colors.
- Draw, erase, and fill pixels.
- Save custom sprites.
- Assign custom sprites to fish, plants, or invertebrates.
- Reuse saved sprites.
- Edit existing sprites.

Optional improvements:

- Template sprites
- Recolor tools
- Simple animations
- Different growth-stage sprites

### 6. Growth Tracking

Organisms can visually and statistically grow over time.

Growth should not be unlimited or random. It should be based on species data and user updates.

**Recommended growth stages:**

- Juvenile
- Sub-adult
- Adult

Growth can be estimated using:

- Date added
- Starting size
- Current size
- Expected adult size
- Species growth data, if available
- User-entered size updates

Users should be able to manually override growth stage because real growth varies.

### 7. Notes and Health Tracking

Users can add notes to tanks and individual organisms.

**Examples:**

- Health status
- Disease symptoms
- Medication notes
- Behavior changes
- Feeding preferences
- Aggression issues
- Plant melting or growth notes
- Breeding notes
- Personal observations

Health statuses could include:

- Healthy
- Watching
- Sick
- Recovering
- Deceased
- Removed
- Unknown

### 8. Water Parameter Logging

Users can store water test results for each tank.

**Common freshwater parameters:**

- Temperature
- pH
- Ammonia
- Nitrite
- Nitrate
- GH
- KH
- TDS
- Phosphate

**Common saltwater parameters:**

- Salinity
- Calcium
- Alkalinity
- Magnesium
- Phosphate
- Nitrate
- Temperature
- pH

Each test entry should include:

- Tank
- Date and time
- Parameter name
- Value
- Unit
- Notes

Users should be able to view recent readings and historical trends.

### 9. Graphs and Visualizations

The app should include charts that help users understand tank health over time.

Useful visualizations:

- pH over time
- Temperature over time
- Ammonia, nitrite, and nitrate trends
- Fertilizer dosing history
- Water change frequency
- Tank health score over time
- Livestock count over time

Graphs should make it easy to spot changes, spikes, or long-term trends.

### 10. Maintenance Logs

Users should be able to record completed aquarium care tasks.

Common tasks:

- Feeding
- Water changes
- Fertilizing
- Filter cleaning
- Glass cleaning
- Substrate vacuuming
- Trimming plants
- Medication dosing
- Equipment changes
- CO₂ adjustments
- Light schedule changes

Each maintenance log should include:

- Tank
- Task type
- Date and time
- Details
- Notes

Example water change log:

- Date
- Percentage changed
- Water conditioner used
- Fertilizer added
- Notes

### 11. Reminders and Notifications

Users can create reminders for aquarium tasks.

Reminder types:

- Feed fish
- Dose fertilizer
- Change water
- Test water
- Clean filter
- Trim plants
- Dose medication
- Replace equipment
- Check livestock health

Reminder fields:

- Title
- Tank
- Task type
- Frequency
- Date and time
- Repeat schedule
- Completion status
- Notes

Users should be able to mark reminders as completed and optionally create a maintenance log from a completed reminder.

### 12. Wishlist

Users can maintain a wishlist of things they may want to buy later.

Wishlist item types:

- Fish
- Invertebrate
- Plant
- Coral
- Equipment
- Decoration
- Food
- Fertilizer
- Medication
- Other

Wishlist fields:

- Item name
- Category
- Desired tank
- Estimated price
- Store or source
- Priority
- Compatibility notes
- Purchase status
- Notes

Optional useful feature:

- Show whether a wishlist organism is likely compatible with the selected tank.

### 13. Compatibility and Stocking Warnings

The app should help users avoid common aquarium mistakes.

Possible warnings:

- Tank may be too small.
- Fish may be aggressive.
- Fish may need a larger group.
- Species may not be shrimp-safe.
- Species may eat plants.
- Temperature ranges do not match.
- pH ranges do not match.
- Tank may be overstocked.
- Species may not be beginner-friendly.

These warnings should be friendly and educational, not overly strict.

### 14. Dashboard

The dashboard should give users a quick overview of their aquarium status.

Possible dashboard sections:

- List of tanks
- Current tank status
- Upcoming reminders
- Recent maintenance
- Latest water test results
- Livestock health alerts
- Wishlist highlights
- Tank health score
- Quick-add buttons

Example dashboard summary:

- Last water change: 8 days ago
- Next feeding: Today at 6:00 PM
- pH: 7.2
- Ammonia: 0 ppm
- Nitrite: 0 ppm
- Nitrate: 20 ppm
- Tank health: Stable

### 15. Tank Health Score

A simple tank health score can make the app more engaging.

The score can be based on:

- Recent water test results
- Stability of parameters
- Missed reminders
- Recent maintenance
- Livestock health statuses
- Overstocking warnings

Example:

```text
Tank Health: 86/100
Status: Stable
Suggestion: Nitrate has been rising. Consider a water change soon.
