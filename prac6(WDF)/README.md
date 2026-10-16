# Practical 6: Rendering External JSON Data using Fetch API, Search and Filter

## 📌 Problem Definition
Display event lists, student profiles, notices, or FAQs from external JSON files using the **Fetch API**. Implement dynamic rendering, real-time search, multi-criteria filtering, multi-field sorting, and client-side pagination.

---

## 🛠️ Key Features Implemented

1. **External JSON Datasets (`data/`) — Expanded to 40 Records Each**
   - `events.json`: **40 college event records** with details on dates, categories, venue, fee, time, organizer, and available seats.
   - `students.json`: **40 student records** with roll numbers, departments, GPAs, technical skills, and hierarchical location fields (`country`, `state`, `city`).
   - `notices.json`: **40 college notices & circular records** across examinations, placements, academics, administration, and campus events.
   - `faqs.json`: **40 categorized FAQ records** with questions, answers, and update timestamps.

2. **Modular Architecture**
   - `data/`: Clean external JSON data sources.
   - `css/style.css`: Simple, clean, human-designed light neutral theme (no bright/neon dark mode) with dedicated spotlight & countdown chip styles.
   - `js/api.js`: Dedicated module handling asynchronous Fetch API requests, HTTP status verification (`response.ok`), error states, and local caching.
   - `js/app.js`: State management, event handling, real-time multi-field search, filtering, sorting, dependent dropdown logic, upcoming events logic, and pagination rendering.
   - `index.html`: Clean, semantic HTML5 structure with tabbed navigation, upcoming spotlight section, and accessible components.

3. **🔔 Upcoming Events Section (Featured & Dedicated)**
   - **Upcoming Spotlight on Events Page:** Automatically calculates date differences from today's date, displays the top 3 nearest events with real-time countdown chips (`Today!`, `Tomorrow`, `⏳ X days left`), available seats, and 1-click Quick Registration.
   - **Dedicated Upcoming Events Tab:** Complete dashboard with live statistics (Total Upcoming, This Week, This Month, Free Entry), interactive filter chips by timeframe and category, search bar, and toast notification confirmation.

4. **Intermediate Extension: Dependent Dropdowns**
   - Hierarchical location filtering: **Country &rarr; State &rarr; City**.
   - Selecting a country dynamically populates only the states belonging to that country.
   - Selecting a state restricts cities to that state.
   - Changing selections automatically updates the filtered dataset in real-time.

5. **Advanced Extension: Response Caching in `localStorage`**
   - Network responses are cached in browser `localStorage` along with retrieval timestamps.
   - Works seamlessly when offline or during intermittent network connectivity.
   - Live status indicator badge displays whether records were loaded fresh from Fetch API or retrieved from cache.
   - User controls provided for "Refresh Data" and "Clear Cache".

6. **Search, Filter, Sort & Pagination**
   - **Search:** Real-time multi-field search across titles, organizers, skills, names, and queries.
   - **Filter:** Category/Department dropdown + dependent location dropdowns.
   - **Sort:** Ascending/descending by Title/Name or Value (GPA / Date).
   - **Pagination:** Configurable page size (6, 10, 20, Show All 40), previous/next buttons, and numbered page buttons with boundary protection.

---

## 📚 Key Questions & Interpretation

### Q1. How is JSON fetched, parsed, and rendered?
1. **Fetch:** An asynchronous HTTP GET request is made using `fetch('data/events.json')`.
2. **Verify:** Check `response.ok` (status code 200–299) to detect HTTP errors.
3. **Parse:** Call `await response.json()` to parse raw JSON into JavaScript objects/arrays.
4. **Render:** The array is iterated using `forEach()` or `map()` to generate semantic HTML cards/tables and injected into the DOM via `element.innerHTML`.

### Q2. Which array methods are used for search, filter, sort, and pagination?
- **`Array.prototype.filter()`**: Evaluates criteria against each item for search queries and category/dependent dropdown matching.
- **`Array.prototype.sort()`**: Compares elements using custom comparator functions (`localeCompare` for alphabetical and arithmetic differences for numbers/dates).
- **`Array.prototype.slice(startIndex, endIndex)`**: Extracts the current page's slice of items from the filtered array.
- **`Array.prototype.map()` & `new Set()`**: Extracts unique lists of categories, countries, and states for dropdown options.

### Q3. How are loading and error states handled?
- **Loading:** Before the asynchronous call finishes, a CSS loading spinner and message are rendered.
- **Error:** Wrapped inside a `try...catch` block. Network failures or invalid responses render an alert box with a user-friendly retry button.
- **Empty State:** If filters return zero matches, an informative empty state message is shown with a "Clear Filters" button.

### Q4. How is modularity maintained in JavaScript files?
- Code is divided by responsibility:
  - `api.js` manages networking, HTTP checks, and storage caching.
  - `app.js` handles DOM manipulation, event listeners, algorithms, and application state.

---

## 💻 How to Run the Project in VS Code

Because modern web browsers enforce security policies (CORS) on `fetch()` when opening files via `file:///`, run the project through a local web server:

### Option 1: VS Code Live Server (Recommended)
1. Install the **Live Server** extension in VS Code (by Ritwick Dey).
2. Right-click on `index.html` in the file explorer.
3. Select **"Open with Live Server"**.
4. The project will open in your default browser at `http://127.0.0.1:5500/index.html`.

### Option 2: Python Built-in Server
Open terminal in the `prac6(WDF)` folder and run:
```bash
python -m http.server 3000
```
Then visit `http://localhost:3000` in your browser.
