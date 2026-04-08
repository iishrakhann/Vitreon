# PuneEventHub Frontend Notes

## Brand System

- `--color-lavender`: `#E6E6FA`
- `--color-plum`: `#301934`
- `--color-mist`: `#F5F5F7`

## UI Decisions

- Glassmorphism panels are powered by a shared frosted glass treatment in [`styles.css`](/C:/Users/ishra/Documents/New%20project/php-app/public/styles.css)
- Mobile discovery keeps the main navigation anchored at the bottom for thumb reach
- Venue cards use hover lift transitions for a premium but restrained motion language

## Integration Hooks

- Replace the discovery map placeholder with a Google Maps embed and venue pin clustering
- Feed `/api/venues/top-rated/{eventType}` from AI-enriched review aggregates instead of in-memory demo data
- Connect owner dashboard metrics to MySQL aggregates and Razorpay webhook outcomes
