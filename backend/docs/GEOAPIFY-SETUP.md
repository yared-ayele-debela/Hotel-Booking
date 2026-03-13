# Geoapify Location Setup

Hotel create/edit forms use [Geoapify](https://www.geoapify.com/) for real-time address and location autocomplete. When adding or updating a hotel, vendors can search for an address and the form auto-fills:

- **Address** – Full street address
- **City** – City name
- **Country** – Country name
- **Latitude / Longitude** – Coordinates for maps and distance calculations

## Setup

1. **Get an API key**
   - Go to [Geoapify MyProjects](https://myprojects.geoapify.com/)
   - Register or log in
   - Create a project (or use the default)
   - Copy your API key from the project dashboard

2. **Configure Laravel**
   - Add to `.env`:
     ```
     GEOAPIFY_API_KEY=your_api_key_here
     ```

3. **Usage**
   - On the hotel create/edit forms, use the "Search location" field
   - Start typing an address or place name
   - Choose a suggestion to fill address, city, country, and coordinates

## API Usage

- Geoapify offers a free tier (e.g. 3,000 requests/day)
- Autocomplete requests are proxied through Laravel so the API key stays server-side
- See [Geoapify Pricing](https://www.geoapify.com/pricing/) for limits
