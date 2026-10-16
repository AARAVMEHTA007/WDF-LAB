/**
 * app.js - Main Application Logic for Practical 6
 * Author: Aarav Mehta
 * 
 * Features:
 * - Tab switching between datasets (Events, Students, FAQs, Lab Manual)
 * - Real-time Search across fields using Array.filter() & String.includes()
 * - Category / Department Filtering
 * - Intermediate Extension: Dependent Dropdowns (Country -> State -> City)
 * - Array Sorting using Array.sort() (Name, Date, GPA, ID)
 * - Complete Pagination Logic (slice, total pages, page buttons)
 * - Advanced Extension: LocalStorage offline caching inspection
 */

// Application State
const state = {
  currentTab: "events",       // 'events' | 'students' | 'faqs' | 'notices' | 'upcoming' | 'manual'
  rawData: [],                // Original array from API
  filteredData: [],           // After search, filter, and sort
  dataSource: "network",      // 'network' | 'cache' | 'fallback'
  dataTimestamp: "",
  
  // Filter & Search controls
  searchQuery: "",
  categoryFilter: "all",
  
  // Dependent dropdowns (Intermediate Extension)
  selectedCountry: "all",
  selectedState: "all",
  selectedCity: "all",
  
  // Sorting
  sortBy: "default",          // 'default' | 'name-asc' | 'name-desc' | 'val-desc' | 'val-asc'
  
  // Pagination
  currentPage: 1,
  itemsPerPage: 6,

  // Upcoming Events section controls
  upcomingFilter: "all",
  upcomingSearch: "",
  allUpcomingEvents: []
};

// DOM Elements
const dom = {
  tabs: document.querySelectorAll(".tab-btn"),
  contentArea: document.getElementById("contentArea"),
  filterCard: document.getElementById("filterCard"),
  resultsHeader: document.getElementById("resultsHeader"),
  paginationContainer: document.getElementById("paginationContainer"),
  
  // Controls
  searchInput: document.getElementById("searchInput"),
  categorySelect: document.getElementById("categorySelect"),
  categoryLabel: document.getElementById("categoryLabel"),
  sortSelect: document.getElementById("sortSelect"),
  pageSizeSelect: document.getElementById("pageSizeSelect"),
  resetFiltersBtn: document.getElementById("resetFiltersBtn"),
  
  // Dependent Dropdown elements
  dependentBox: document.getElementById("dependentBox"),
  countrySelect: document.getElementById("countrySelect"),
  stateSelect: document.getElementById("stateSelect"),
  citySelect: document.getElementById("citySelect"),
  resetGeoBtn: document.getElementById("resetGeoBtn"),
  
  // Results count
  resultsInfo: document.getElementById("resultsInfo"),
  
  // Cache bar
  cacheStatusText: document.getElementById("cacheStatusText"),
  statusDot: document.getElementById("statusDot"),
  refreshDataBtn: document.getElementById("refreshDataBtn"),
  clearCacheBtn: document.getElementById("clearCacheBtn"),

  // Upcoming Events Spotlight & Toast Notification
  upcomingSpotlightSection: document.getElementById("upcomingSpotlightSection"),
  toastNotification: document.getElementById("toastNotification")
};

// ========================================================
// 1. Initial Load & Event Listeners
// ========================================================
document.addEventListener("DOMContentLoaded", () => {
  initEventListeners();
  loadCurrentDataset();
  // Pre-load events data to count upcoming and set badge
  updateUpcomingBadge();
});

function initEventListeners() {
  // Tab switching
  dom.tabs.forEach(btn => {
    btn.addEventListener("click", () => {
      const targetTab = btn.getAttribute("data-tab");
      if (targetTab === state.currentTab) return;
      
      dom.tabs.forEach(t => t.classList.remove("active"));
      btn.classList.add("active");
      
      switchTab(targetTab);
    });
  });

  // Search input (with debounce or direct input)
  dom.searchInput.addEventListener("input", (e) => {
    state.searchQuery = e.target.value.trim().toLowerCase();
    state.currentPage = 1;
    applyFiltersAndRender();
  });

  // Category filter
  dom.categorySelect.addEventListener("change", (e) => {
    state.categoryFilter = e.target.value;
    state.currentPage = 1;
    applyFiltersAndRender();
  });

  // Sort dropdown
  dom.sortSelect.addEventListener("change", (e) => {
    state.sortBy = e.target.value;
    state.currentPage = 1;
    applyFiltersAndRender();
  });

  // Page size dropdown
  dom.pageSizeSelect.addEventListener("change", (e) => {
    const val = e.target.value;
    state.itemsPerPage = val === "all" ? 9999 : parseInt(val, 10);
    state.currentPage = 1;
    applyFiltersAndRender();
  });

  // Reset all filters
  dom.resetFiltersBtn.addEventListener("click", resetAllFilters);

  // Dependent dropdowns (Intermediate Extension)
  dom.countrySelect.addEventListener("change", onCountryChange);
  dom.stateSelect.addEventListener("change", onStateChange);
  dom.citySelect.addEventListener("change", onCityChange);
  dom.resetGeoBtn.addEventListener("click", resetGeoFilters);

  // Cache actions (Advanced Extension)
  dom.refreshDataBtn.addEventListener("click", () => {
    loadCurrentDataset(true); // force refresh
  });

  dom.clearCacheBtn.addEventListener("click", () => {
    ApiService.clearCache(state.currentTab);
    alert(`Cache cleared for "${state.currentTab}". Refreshing from network...`);
    loadCurrentDataset(true);
  });
}

// ========================================================
// 2. Data Fetching & State Handling
// ========================================================
async function loadCurrentDataset(forceRefresh = false) {
  if (state.currentTab === "manual") {
    renderLabManual();
    return;
  }

  if (state.currentTab === "upcoming") {
    dom.filterCard.style.display = "none";
    dom.resultsHeader.style.display = "none";
    dom.paginationContainer.style.display = "none";
    dom.dependentBox.style.display = "none";
    if (dom.upcomingSpotlightSection) dom.upcomingSpotlightSection.style.display = "none";
    renderUpcomingEventsView();
    return;
  }

  // Show UI controls for data datasets
  dom.filterCard.style.display = "block";
  dom.resultsHeader.style.display = "flex";
  dom.paginationContainer.style.display = "flex";

  // Show Upcoming Events Spotlight on Events tab
  if (state.currentTab === "events") {
    if (dom.upcomingSpotlightSection) dom.upcomingSpotlightSection.style.display = "block";
  } else {
    if (dom.upcomingSpotlightSection) dom.upcomingSpotlightSection.style.display = "none";
  }

  // Show Dependent Dropdowns only for student profiles
  if (state.currentTab === "students") {
    dom.dependentBox.style.display = "grid";
  } else {
    dom.dependentBox.style.display = "none";
  }

  // Render loading state
  dom.contentArea.innerHTML = `
    <div class="loading-box">
      <div class="spinner"></div>
      <p>Fetching <strong>${state.currentTab}.json</strong> via Fetch API...</p>
    </div>
  `;

  try {
    const result = await ApiService.fetchData(state.currentTab, forceRefresh);
    state.rawData = result.data;
    state.dataSource = result.source;
    state.dataTimestamp = result.timestamp;

    updateCacheBar();
    populateCategoryOptions();
    
    if (state.currentTab === "events") {
      renderUpcomingSpotlight();
    }

    if (state.currentTab === "students") {
      populateDependentDropdowns();
    }

    applyFiltersAndRender();

  } catch (error) {
    console.error("Data load error:", error);
    renderErrorState(error.message);
  }
}

function updateCacheBar() {
  if (state.dataSource === "network") {
    dom.statusDot.className = "status-dot";
    dom.cacheStatusText.innerHTML = `Source: <strong>Fetch API (Network)</strong> &bull; Fetched at: ${state.dataTimestamp}`;
  } else if (state.dataSource === "cache") {
    dom.statusDot.className = "status-dot cached";
    dom.cacheStatusText.innerHTML = `Source: <strong>localStorage Cache (Offline Mode)</strong> &bull; Cached at: ${state.dataTimestamp}`;
  } else {
    dom.statusDot.className = "status-dot";
    dom.cacheStatusText.innerHTML = `Source: <strong>Local Fallback Data</strong>`;
  }
}

function renderErrorState(errorMessage) {
  dom.resultsHeader.style.display = "none";
  dom.paginationContainer.style.display = "none";
  dom.contentArea.innerHTML = `
    <div class="error-box">
      <h3>Failed to Load Data</h3>
      <p>${errorMessage}</p>
      <p style="margin-top: 8px; font-size: 0.88rem; color: #7f1d1d;">
        Tip: If running locally without a web server, launch with VS Code Live Server to permit Fetch API requests.
      </p>
      <button onclick="loadCurrentDataset(true)">Try Again</button>
    </div>
  `;
}

// ========================================================
// 3. Tab Switching
// ========================================================
function switchTab(tabKey) {
  state.currentTab = tabKey;
  state.currentPage = 1;
  state.searchQuery = "";
  dom.searchInput.value = "";
  state.categoryFilter = "all";
  resetGeoFiltersState();

  if (dom.upcomingSpotlightSection) {
    dom.upcomingSpotlightSection.style.display = tabKey === "events" ? "block" : "none";
  }

  if (tabKey === "manual" || tabKey === "upcoming") {
    dom.filterCard.style.display = "none";
    dom.resultsHeader.style.display = "none";
    dom.paginationContainer.style.display = "none";
    if (tabKey === "manual") renderLabManual();
    else renderUpcomingEventsView();
  } else {
    loadCurrentDataset();
  }
}

// ========================================================
// 4. Category & Dependent Dropdowns Setup
// ========================================================
function populateCategoryOptions() {
  dom.categorySelect.innerHTML = '<option value="all">All Categories</option>';

  if (state.currentTab === "events") {
    dom.categoryLabel.textContent = "Event Category:";
    const categories = [...new Set(state.rawData.map(item => item.category))];
    categories.forEach(cat => {
      dom.categorySelect.innerHTML += `<option value="${cat}">${cat}</option>`;
    });
  } else if (state.currentTab === "students") {
    dom.categoryLabel.textContent = "Department:";
    const depts = [...new Set(state.rawData.map(item => item.department))];
    depts.forEach(d => {
      dom.categorySelect.innerHTML += `<option value="${d}">${d}</option>`;
    });
  } else if (state.currentTab === "faqs") {
    dom.categoryLabel.textContent = "FAQ Topic:";
    const topics = [...new Set(state.rawData.map(item => item.category))];
    topics.forEach(t => {
      dom.categorySelect.innerHTML += `<option value="${t}">${t}</option>`;
    });
  } else if (state.currentTab === "notices") {
    dom.categoryLabel.textContent = "Notice Category:";
    const categories = [...new Set(state.rawData.map(item => item.category))];
    categories.forEach(cat => {
      dom.categorySelect.innerHTML += `<option value="${cat}">${cat}</option>`;
    });
  }
}

/**
 * Intermediate Extension: Country -> State -> City Dependent Dropdown logic
 */
function populateDependentDropdowns() {
  const countries = [...new Set(state.rawData.map(s => s.country).filter(Boolean))];
  
  dom.countrySelect.innerHTML = '<option value="all">All Countries</option>';
  countries.forEach(c => {
    dom.countrySelect.innerHTML += `<option value="${c}">${c}</option>`;
  });

  populateStateDropdown();
  populateCityDropdown();
}

function populateStateDropdown() {
  let list = state.rawData;
  if (state.selectedCountry !== "all") {
    list = list.filter(s => s.country === state.selectedCountry);
  }
  const states = [...new Set(list.map(s => s.state).filter(Boolean))];

  dom.stateSelect.innerHTML = '<option value="all">All States</option>';
  states.forEach(st => {
    dom.stateSelect.innerHTML += `<option value="${st}">${st}</option>`;
  });
  dom.stateSelect.value = state.selectedState;
}

function populateCityDropdown() {
  let list = state.rawData;
  if (state.selectedCountry !== "all") {
    list = list.filter(s => s.country === state.selectedCountry);
  }
  if (state.selectedState !== "all") {
    list = list.filter(s => s.state === state.selectedState);
  }
  const cities = [...new Set(list.map(s => s.city).filter(Boolean))];

  dom.citySelect.innerHTML = '<option value="all">All Cities</option>';
  cities.forEach(city => {
    dom.citySelect.innerHTML += `<option value="${city}">${city}</option>`;
  });
  dom.citySelect.value = state.selectedCity;
}

function onCountryChange(e) {
  state.selectedCountry = e.target.value;
  state.selectedState = "all";
  state.selectedCity = "all";
  populateStateDropdown();
  populateCityDropdown();
  state.currentPage = 1;
  applyFiltersAndRender();
}

function onStateChange(e) {
  state.selectedState = e.target.value;
  state.selectedCity = "all";
  populateCityDropdown();
  state.currentPage = 1;
  applyFiltersAndRender();
}

function onCityChange(e) {
  state.selectedCity = e.target.value;
  state.currentPage = 1;
  applyFiltersAndRender();
}

function resetGeoFiltersState() {
  state.selectedCountry = "all";
  state.selectedState = "all";
  state.selectedCity = "all";
  if (dom.countrySelect) dom.countrySelect.value = "all";
  if (dom.stateSelect) dom.stateSelect.value = "all";
  if (dom.citySelect) dom.citySelect.value = "all";
}

function resetGeoFilters() {
  resetGeoFiltersState();
  if (state.currentTab === "students") {
    populateStateDropdown();
    populateCityDropdown();
  }
  state.currentPage = 1;
  applyFiltersAndRender();
}

function resetAllFilters() {
  state.searchQuery = "";
  dom.searchInput.value = "";
  state.categoryFilter = "all";
  dom.categorySelect.value = "all";
  state.sortBy = "default";
  dom.sortSelect.value = "default";
  resetGeoFiltersState();
  if (state.currentTab === "students") {
    populateStateDropdown();
    populateCityDropdown();
  }
  state.currentPage = 1;
  applyFiltersAndRender();
}

// ========================================================
// 5. Search, Filter, Sort & Pagination Algorithms
// ========================================================
function applyFiltersAndRender() {
  let result = [...state.rawData];

  // A. SEARCH LOGIC (Multi-field Array.filter)
  if (state.searchQuery) {
    const q = state.searchQuery;
    result = result.filter(item => {
      if (state.currentTab === "events") {
        return (
          item.title.toLowerCase().includes(q) ||
          item.category.toLowerCase().includes(q) ||
          item.organizer.toLowerCase().includes(q) ||
          item.venue.toLowerCase().includes(q) ||
          item.description.toLowerCase().includes(q)
        );
      } else if (state.currentTab === "students") {
        const skillsMatch = Array.isArray(item.skills) && item.skills.some(s => s.toLowerCase().includes(q));
        return (
          item.name.toLowerCase().includes(q) ||
          item.rollNo.toLowerCase().includes(q) ||
          item.department.toLowerCase().includes(q) ||
          item.city.toLowerCase().includes(q) ||
          item.email.toLowerCase().includes(q) ||
          skillsMatch
        );
      } else if (state.currentTab === "notices") {
        return (
          item.title.toLowerCase().includes(q) ||
          item.category.toLowerCase().includes(q) ||
          (item.issuedBy && item.issuedBy.toLowerCase().includes(q)) ||
          (item.targetAudience && item.targetAudience.toLowerCase().includes(q)) ||
          item.description.toLowerCase().includes(q)
        );
      } else if (state.currentTab === "faqs") {
        return (
          item.question.toLowerCase().includes(q) ||
          item.answer.toLowerCase().includes(q) ||
          item.category.toLowerCase().includes(q)
        );
      }
      return false;
    });
  }

  // B. CATEGORY FILTER
  if (state.categoryFilter !== "all") {
    result = result.filter(item => {
      if (state.currentTab === "events") return item.category === state.categoryFilter;
      if (state.currentTab === "students") return item.department === state.categoryFilter;
      if (state.currentTab === "notices") return item.category === state.categoryFilter;
      if (state.currentTab === "faqs") return item.category === state.categoryFilter;
      return true;
    });
  }

  // C. DEPENDENT DROPDOWN GEO FILTER (Students)
  if (state.currentTab === "students") {
    if (state.selectedCountry !== "all") {
      result = result.filter(s => s.country === state.selectedCountry);
    }
    if (state.selectedState !== "all") {
      result = result.filter(s => s.state === state.selectedState);
    }
    if (state.selectedCity !== "all") {
      result = result.filter(s => s.city === state.selectedCity);
    }
  }

  // D. SORTING LOGIC
  if (state.sortBy !== 'default') {
    result.sort((a, b) => {
      if (state.sortBy === 'name-asc') {
        const nameA = a.name || a.title || a.question || '';
        const nameB = b.name || b.title || b.question || '';
        return nameA.localeCompare(nameB);
      }
      if (state.sortBy === 'name-desc') {
        const nameA = a.name || a.title || a.question || '';
        const nameB = b.name || b.title || b.question || '';
        return nameB.localeCompare(nameA);
      }
      if (state.sortBy === 'val-desc') {
        if (state.currentTab === 'students') return (b.gpa || 0) - (a.gpa || 0);
        if (state.currentTab === 'events' || state.currentTab === 'notices') return new Date(b.date) - new Date(a.date);
        return (b.id || 0) - (a.id || 0);
      }
      if (state.sortBy === 'val-asc') {
        if (state.currentTab === 'students') return (a.gpa || 0) - (b.gpa || 0);
        if (state.currentTab === 'events' || state.currentTab === 'notices') return new Date(a.date) - new Date(b.date);
        return (a.id || 0) - (b.id || 0);
      }
      // Column sort: id-asc / id-desc
      if (state.sortBy === 'id-asc')  return (a.id || 0) - (b.id || 0);
      if (state.sortBy === 'id-desc') return (b.id || 0) - (a.id || 0);
      // Column sort: sem-asc / sem-desc (students only)
      if (state.sortBy === 'sem-asc')  return (a.semester || 0) - (b.semester || 0);
      if (state.sortBy === 'sem-desc') return (b.semester || 0) - (a.semester || 0);
      return 0;
    });
  }

  state.filteredData = result;

  // Render Views
  renderResultsCount();
  renderCurrentPageItems();
  renderPaginationControls();
}

// ========================================================
// 6. View Renderers (Events, Students, FAQs)
// ========================================================
function renderResultsCount() {
  const total = state.filteredData.length;
  const original = state.rawData.length;
  
  if (total === 0) {
    dom.resultsInfo.innerHTML = `No records matching current filters (out of <strong>${original}</strong> total).`;
  } else {
    const start = (state.currentPage - 1) * state.itemsPerPage + 1;
    const end = Math.min(state.currentPage * state.itemsPerPage, total);
    dom.resultsInfo.innerHTML = `Showing <strong>${start} - ${end}</strong> of <strong>${total}</strong> records (Total: ${original})`;
  }
}

function renderCurrentPageItems() {
  const total = state.filteredData.length;
  if (total === 0) {
    dom.contentArea.innerHTML = `
      <div class="empty-box">
        <h3>No matching records found</h3>
        <p>Try refining your search keyword or clearing active filters.</p>
        <button class="btn-secondary" style="margin-top: 12px;" onclick="resetAllFilters()">Clear All Filters</button>
      </div>
    `;
    return;
  }

  // Calculate slice for current page
  const startIndex = (state.currentPage - 1) * state.itemsPerPage;
  const endIndex = startIndex + state.itemsPerPage;
  const pageItems = state.filteredData.slice(startIndex, endIndex);

  if (state.currentTab === "events") {
    renderEventsView(pageItems);
  } else if (state.currentTab === "students") {
    renderStudentsView(pageItems);
  } else if (state.currentTab === "notices") {
    renderNoticesView(pageItems);
  } else if (state.currentTab === "faqs") {
    renderFaqsView(pageItems);
  }
}

function renderEventsView(items) {
  let html = '<div class="events-grid">';
  items.forEach((ev, idx) => {
    const badgeClass = ev.category.toLowerCase();
    html += `
      <div class="event-card" style="animation: cardFadeIn 0.3s ease both; animation-delay: ${idx * 50}ms">
        <div class="event-meta-top">
          <span class="badge ${badgeClass}">${ev.category}</span>
          <span class="event-fee">${ev.fee}</span>
        </div>
        <h3>${ev.title}</h3>
        <p class="event-desc">${ev.description}</p>
        <div class="event-details">
          <span>📅 Date: ${ev.date} (${ev.time})</span>
          <span>📍 Venue: ${ev.venue}</span>
          <span>👥 Organizer: ${ev.organizer}</span>
          <span>🎟️ Available Seats: ${ev.seatsAvailable}</span>
        </div>
      </div>
    `;
  });
  html += '</div>';
  dom.contentArea.innerHTML = html;
}

function renderStudentsView(items) {
  // Determine sort arrow for each sortable column
  function sortArrow(colKey) {
    if (state.sortBy === colKey + '-asc')  return ' <span class="sort-arrow asc">▲</span>';
    if (state.sortBy === colKey + '-desc') return ' <span class="sort-arrow desc">▼</span>';
    return ' <span class="sort-arrow neutral">⇅</span>';
  }

  let html = `
    <div class="table-responsive">
      <table class="data-table" id="studentsTable">
        <thead>
          <tr>
            <th class="sortable-col" onclick="sortByColumn('id-asc','id-desc')" title="Sort by Roll No">
              Roll No${sortArrow('id')}
            </th>
            <th class="sortable-col" onclick="sortByColumn('name-asc','name-desc')" title="Sort by Name">
              Student Name${sortArrow('name')}
            </th>
            <th>Department</th>
            <th class="sortable-col" onclick="sortByColumn('sem-asc','sem-desc')" title="Sort by Semester">
              Sem${sortArrow('sem')}
            </th>
            <th>Location</th>
            <th class="sortable-col" onclick="sortByColumn('val-desc','val-asc')" title="Sort by CGPA">
              CGPA${sortArrow('val')}
            </th>
            <th>Skills</th>
          </tr>
        </thead>
        <tbody>
  `;

  items.forEach((st, idx) => {
    const skillsHtml = st.skills ? st.skills.map(s => `<span class="skill-pill">${s}</span>`).join(' ') : '-';
    html += `
      <tr style="animation: rowFadeIn 0.25s ease both; animation-delay: ${idx * 30}ms">
        <td><strong>${st.rollNo}</strong></td>
        <td>
          <div>${st.name}</div>
          <small style="color: #6b7280;">${st.email}</small>
        </td>
        <td>${st.department}</td>
        <td>Sem ${st.semester}</td>
        <td>${st.city}, ${st.state}</td>
        <td><span class="gpa-badge">${st.gpa.toFixed(2)}</span></td>
        <td>${skillsHtml}</td>
      </tr>
    `;
  });

  html += `
        </tbody>
      </table>
    </div>
  `;
  dom.contentArea.innerHTML = html;
}

function renderNoticesView(items) {
  let html = '<div class="notices-grid">';
  items.forEach((notice, idx) => {
    const priorityClass = notice.priority ? notice.priority.toLowerCase() : 'general';
    const catClass = notice.category ? notice.category.toLowerCase() : '';
    html += `
      <div class="notice-card priority-border-${priorityClass}" style="animation: cardFadeIn 0.3s ease both; animation-delay: ${idx * 50}ms">
        <div class="notice-meta-top">
          <div class="notice-badges">
            <span class="badge ${catClass}">${notice.category}</span>
            <span class="priority-pill priority-${priorityClass}">● ${notice.priority}</span>
          </div>
          <span class="notice-date">📅 ${notice.date}</span>
        </div>
        <h3 class="notice-title">${notice.title}</h3>
        <p class="notice-desc">${notice.description}</p>
        <div class="notice-details">
          <span>🏛️ <strong>Issued By:</strong> ${notice.issuedBy}</span>
          <span>👥 <strong>Target:</strong> ${notice.targetAudience}</span>
        </div>
      </div>
    `;
  });
  html += '</div>';
  dom.contentArea.innerHTML = html;
}

function renderFaqsView(items) {
  let html = '<div class="faqs-list">';
  items.forEach((faq, idx) => {
    html += `
      <div class="faq-item" style="animation: cardFadeIn 0.3s ease both; animation-delay: ${idx * 40}ms">
        <div class="faq-header">
          <h4 class="faq-question">Q: ${faq.question}</h4>
          <span class="badge">${faq.category}</span>
        </div>
        <p class="faq-answer">${faq.answer}</p>
        <div class="faq-meta">Last Updated: ${faq.updated}</div>
      </div>
    `;
  });
  html += '</div>';
  dom.contentArea.innerHTML = html;
}

// ========================================================
// 7. Pagination Controls
// ========================================================
function renderPaginationControls() {
  const total = state.filteredData.length;
  const totalPages = Math.ceil(total / state.itemsPerPage);

  if (totalPages <= 1) {
    dom.paginationContainer.innerHTML = `
      <span class="page-info-text">Showing all ${total} record${total !== 1 ? 's' : ''}</span>
    `;
    return;
  }

  const cur = state.currentPage;

  // Build smart page number list with ellipsis
  function buildPageList(cur, total) {
    const delta = 2; // pages around current
    const range = [];
    const rangeWithDots = [];
    let l;

    for (let i = 1; i <= total; i++) {
      if (i === 1 || i === total || (i >= cur - delta && i <= cur + delta)) {
        range.push(i);
      }
    }

    for (let i of range) {
      if (l) {
        if (i - l === 2) {
          rangeWithDots.push(l + 1); // single gap — just show number
        } else if (i - l > 2) {
          rangeWithDots.push('...');
        }
      }
      rangeWithDots.push(i);
      l = i;
    }
    return rangeWithDots;
  }

  const pageList = buildPageList(cur, totalPages);

  let pagesHtml = '';
  pageList.forEach(p => {
    if (p === '...') {
      pagesHtml += `<span class="page-ellipsis">…</span>`;
    } else {
      pagesHtml += `<button class="page-btn${p === cur ? ' active' : ''}" onclick="goToPage(${p})" aria-label="Page ${p}">${p}</button>`;
    }
  });

  dom.paginationContainer.innerHTML = `
    <div class="pagination-info">
      <span class="page-info-text">Page <strong>${cur}</strong> of <strong>${totalPages}</strong></span>
    </div>
    <div class="pagination-pages">
      <button class="page-btn page-nav" ${cur === 1 ? 'disabled' : ''} onclick="goToPage(1)" title="First page">«</button>
      <button class="page-btn page-nav" ${cur === 1 ? 'disabled' : ''} onclick="goToPage(${cur - 1})" title="Previous page">‹ Prev</button>
      ${pagesHtml}
      <button class="page-btn page-nav" ${cur === totalPages ? 'disabled' : ''} onclick="goToPage(${cur + 1})" title="Next page">Next ›</button>
      <button class="page-btn page-nav" ${cur === totalPages ? 'disabled' : ''} onclick="goToPage(${totalPages})" title="Last page">»</button>
    </div>
  `;
}

window.goToPage = function(pageNum) {
  const totalPages = Math.ceil(state.filteredData.length / state.itemsPerPage);
  if (pageNum < 1 || pageNum > totalPages) return;
  state.currentPage = pageNum;
  renderResultsCount();
  renderCurrentPageItems();
  renderPaginationControls();
  window.scrollTo({ top: dom.filterCard.offsetTop - 20, behavior: 'smooth' });
};

// Column sort helper for student table headers
window.sortByColumn = function(ascKey, descKey) {
  // Toggle: if already on ascKey switch to desc, else go asc
  if (state.sortBy === ascKey) {
    state.sortBy = descKey;
  } else {
    state.sortBy = ascKey;
  }
  dom.sortSelect.value = state.sortBy;
  state.currentPage = 1;
  applyFiltersAndRender();
};

// ========================================================
// 8. Lab Manual, Viva Voce & Question Analysis Tab
// ========================================================
function renderLabManual() {
  dom.contentArea.innerHTML = `
    <div class="manual-container">
      <div class="manual-section">
        <h2>Practical 6: Lab Assignment Analysis & Answers</h2>
        <p><strong>Course:</strong> Web Development Frameworks (WDF-LAB) | <strong>CO Mapping:</strong> CO3, CO4</p>
        <p><strong>Problem Statement:</strong> Display event lists, student profiles, notices, or FAQs from external JSON files using Fetch API. Implement dynamic rendering, search, filter, sorting, and pagination.</p>
      </div>

      <div class="manual-section">
        <h2>Key Lab Questions & Interpretations</h2>

        <div class="qna-card">
          <div class="q">Q1. How is JSON fetched, parsed, and rendered?</div>
          <div class="a">
            External JSON data is requested asynchronously via the modern ES6 <span class="code-inline">fetch(url)</span> method. The method returns a Promise resolving to a Response object. We first verify <span class="code-inline">response.ok</span> (HTTP 200-299 status code) and then call <span class="code-inline">await response.json()</span> to parse the JSON string into native JavaScript objects/arrays. Finally, the parsed array is iterated (using <span class="code-inline">forEach()</span> or <span class="code-inline">map()</span>) to generate HTML string markup which is injected into the DOM via <span class="code-inline">innerHTML</span>.
          </div>
        </div>

        <div class="qna-card">
          <div class="q">Q2. Which array methods are used for search, filter, sort, and pagination?</div>
          <div class="a">
            <ul>
              <li><strong>Search & Filter:</strong> <span class="code-inline">Array.prototype.filter()</span> combined with string methods like <span class="code-inline">toLowerCase().includes()</span> or <span class="code-inline">some()</span>.</li>
              <li><strong>Sort:</strong> <span class="code-inline">Array.prototype.sort((a, b) => compareFunction)</span> using <span class="code-inline">String.prototype.localeCompare()</span> for strings and arithmetic subtraction for numbers/dates.</li>
              <li><strong>Pagination:</strong> <span class="code-inline">Array.prototype.slice(startIndex, endIndex)</span> extracts only the chunk of records corresponding to the active page index.</li>
              <li><strong>Dropdown Population:</strong> <span class="code-inline">Array.prototype.map()</span> and <span class="code-inline">new Set()</span> extract unique categories, countries, and states without duplicates.</li>
            </ul>
          </div>
        </div>

        <div class="qna-card">
          <div class="q">Q3. How are loading and error states handled?</div>
          <div class="a">
            Before the asynchronous <span class="code-inline">fetch()</span> call starts, a loading spinner and message are rendered inside the container. Using a structured <span class="code-inline">try...catch</span> block, network errors or non-200 HTTP codes trigger an error card with descriptive diagnostics and a "Try Again" retry button. An empty state handler also shows user feedback if a search query yields 0 results.
          </div>
        </div>

        <div class="qna-card">
          <div class="q">Q4. How is modularity maintained in JavaScript files?</div>
          <div class="a">
            Separation of concerns is strictly preserved by dividing the JavaScript logic into two dedicated modules:
            <ul>
              <li><span class="code-inline">js/api.js</span>: Encapsulates network operations, HTTP error detection, fallback recovery, and localStorage caching logic.</li>
              <li><span class="code-inline">js/app.js</span>: Manages application UI state, event handling, filter algorithms, dependent dropdowns, and DOM updates.</li>
            </ul>
          </div>
        </div>
      </div>

      <div class="manual-section">
        <h2>Extensions Implemented</h2>
        <div class="qna-card">
          <div class="q">Intermediate Extension: Dependent Dropdowns (Country &rarr; State &rarr; City)</div>
          <div class="a">
            Implemented under the <strong>Student Profiles</strong> tab. Selecting a country dynamically filters the available states; selecting a state dynamically restricts the available cities; and changing the city refines the rendered student table in real-time.
          </div>
        </div>

        <div class="qna-card">
          <div class="q">Advanced Extension: Response Caching in localStorage</div>
          <div class="a">
            When JSON is fetched, the payload and timestamp are stored in <span class="code-inline">localStorage</span>. If the user goes offline or refreshes, the app transparently serves the cached payload with a status indicator badge, and gives user controls to "Refresh Data" or "Clear Cache".
          </div>
        </div>
      </div>
    </div>
  `;
}

// ========================================================
// ========================================================
// 9. Upcoming Events Section & Views
// ========================================================

/**
 * Render the Upcoming Events Spotlight section at the top of the Events page
 */
async function renderUpcomingSpotlight() {
  if (!dom.upcomingSpotlightSection) return;

  let events = state.rawData;
  if (!events || events.length === 0 || state.currentTab !== "events") {
    try {
      const res = await ApiService.fetchData("events");
      events = res.data;
    } catch (_) {
      dom.upcomingSpotlightSection.style.display = "none";
      return;
    }
  }

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  let upcoming = events.filter(ev => new Date(ev.date) >= today);
  if (upcoming.length === 0) upcoming = [...events];
  upcoming.sort((a, b) => new Date(a.date) - new Date(b.date));

  const spotlightItems = upcoming.slice(0, 3); // top 3 nearest events

  let cardsHtml = '';
  spotlightItems.forEach(ev => {
    const days = daysUntil(ev.date);
    const chipClass = days === 0 ? 'soon' : days <= 7 ? 'soon' : days <= 30 ? 'upcoming' : 'far';
    const chipLabel = days === 0 ? '🔴 Today!' : days === 1 ? '🟠 Tomorrow' : `⏳ ${days} days left`;
    const formattedDate = new Date(ev.date).toLocaleDateString('en-IN', {
      month: 'short', day: 'numeric', year: 'numeric'
    });

    cardsHtml += `
      <div class="upcoming-spotlight-card">
        <div class="spotlight-card-top">
          <span class="badge ${ev.category.toLowerCase()}">${ev.category}</span>
          <span class="countdown-chip ${chipClass}">${chipLabel}</span>
        </div>
        <h3 class="spotlight-card-title">${ev.title}</h3>
        <p class="spotlight-card-desc">${ev.description.substring(0, 95)}...</p>
        <div class="spotlight-card-meta">
          <span>📅 ${formattedDate} &bull; ${ev.time}</span>
          <span>📍 ${ev.venue}</span>
        </div>
        <div class="spotlight-card-action">
          <span class="seats-badge">🎟️ ${ev.seatsAvailable} seats left</span>
          <button type="button" class="quick-register-btn" onclick="registerForEvent(${ev.id}, '${ev.title.replace(/'/g, "\\'")}')">
            ⚡ Quick Register
          </button>
        </div>
      </div>
    `;
  });

  dom.upcomingSpotlightSection.innerHTML = `
    <div class="upcoming-spotlight-header">
      <div class="spotlight-title-group">
        <h2>⚡ Upcoming Campus Events Spotlight</h2>
        <p>Nearest events happening soon on campus &bull; Dynamic JSON Fetch API rendering</p>
      </div>
      <button type="button" class="btn-view-all-upcoming" onclick="switchToUpcomingTab()">
        <span>🔔 View All Upcoming Events (${upcoming.length}) &rarr;</span>
      </button>
    </div>
    <div class="upcoming-spotlight-cards">
      ${cardsHtml}
    </div>
  `;
}

/**
 * Dedicated Upcoming Events Tab View
 */
async function renderUpcomingEventsView() {
  dom.contentArea.innerHTML = `
    <div class="loading-box">
      <div class="spinner"></div>
      <p>Loading <strong>upcoming events</strong> via Fetch API...</p>
    </div>
  `;

  let allEvents = [];
  try {
    const result = await ApiService.fetchData("events", true);
    allEvents = result.data;
  } catch (e) {
    dom.contentArea.innerHTML = `<div class="error-box"><h3>Failed to load events</h3><p>${e.message}</p></div>`;
    return;
  }

  const today = new Date();
  today.setHours(0, 0, 0, 0);

  let upcoming = allEvents.filter(ev => new Date(ev.date) >= today);
  if (upcoming.length === 0) upcoming = [...allEvents];
  upcoming.sort((a, b) => new Date(a.date) - new Date(b.date));

  state.allUpcomingEvents = upcoming;
  state.upcomingFilter = "all";
  state.upcomingSearch = "";

  // Update tab badge
  const badge = document.getElementById('upcomingBadge');
  if (badge) badge.textContent = upcoming.length || '';

  renderUpcomingEventsContent();
}

/**
 * Render the filtered content and controls of Upcoming Events view
 */
function renderUpcomingEventsContent() {
  const upcoming = state.allUpcomingEvents || [];
  const soonCount = upcoming.filter(ev => daysUntil(ev.date) <= 7).length;
  const thisMonth = upcoming.filter(ev => daysUntil(ev.date) <= 30).length;
  const freeCount = upcoming.filter(ev => ev.fee === 'Free').length;

  // Apply sub-filter and search
  let filtered = [...upcoming];

  if (state.upcomingFilter === 'this-week') {
    filtered = filtered.filter(ev => daysUntil(ev.date) <= 7);
  } else if (state.upcomingFilter === 'this-month') {
    filtered = filtered.filter(ev => daysUntil(ev.date) <= 30);
  } else if (state.upcomingFilter === 'free') {
    filtered = filtered.filter(ev => ev.fee === 'Free');
  } else if (state.upcomingFilter !== 'all') {
    filtered = filtered.filter(ev => ev.category.toLowerCase() === state.upcomingFilter.toLowerCase());
  }

  if (state.upcomingSearch) {
    const q = state.upcomingSearch.toLowerCase();
    filtered = filtered.filter(ev => 
      ev.title.toLowerCase().includes(q) ||
      ev.category.toLowerCase().includes(q) ||
      ev.venue.toLowerCase().includes(q) ||
      ev.organizer.toLowerCase().includes(q) ||
      ev.description.toLowerCase().includes(q)
    );
  }

  // Hero banner
  let html = `
    <div class="upcoming-hero">
      <div class="upcoming-hero-text">
        <h2>🔔 Upcoming Campus Events Section</h2>
        <p>Discover workshops, hackathons, seminars, and competitions. Register early to reserve your seat!</p>
      </div>
      <div class="upcoming-hero-stats">
        <div class="hero-stat">
          <span class="hero-stat-num">${upcoming.length}</span>
          <span class="hero-stat-label">Total</span>
        </div>
        <div class="hero-stat">
          <span class="hero-stat-num">${soonCount}</span>
          <span class="hero-stat-label">This Week</span>
        </div>
        <div class="hero-stat">
          <span class="hero-stat-num">${thisMonth}</span>
          <span class="hero-stat-label">This Month</span>
        </div>
        <div class="hero-stat">
          <span class="hero-stat-num">${freeCount}</span>
          <span class="hero-stat-label">Free Entry</span>
        </div>
      </div>
    </div>
  `;

  // Interactive controls (Search + Filter Chips)
  html += `
    <div class="upcoming-controls">
      <div class="upcoming-search-row">
        <input 
          type="text" 
          class="upcoming-search-input" 
          id="upcomingSearchInput"
          placeholder="🔍 Search upcoming events by title, keyword, venue, organizer..." 
          value="${state.upcomingSearch || ''}"
          oninput="searchUpcomingEvents(this.value)"
        >
      </div>
      <div class="upcoming-filter-chips">
        <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-right: 4px;">Filter:</span>
        <button class="filter-chip-btn ${state.upcomingFilter === 'all' ? 'active' : ''}" onclick="filterUpcomingEvents('all', this)">All (${upcoming.length})</button>
        <button class="filter-chip-btn ${state.upcomingFilter === 'this-week' ? 'active' : ''}" onclick="filterUpcomingEvents('this-week', this)">🔥 This Week (${soonCount})</button>
        <button class="filter-chip-btn ${state.upcomingFilter === 'this-month' ? 'active' : ''}" onclick="filterUpcomingEvents('this-month', this)">📅 This Month (${thisMonth})</button>
        <button class="filter-chip-btn ${state.upcomingFilter === 'free' ? 'active' : ''}" onclick="filterUpcomingEvents('free', this)">🎟️ Free Entry (${freeCount})</button>
        <button class="filter-chip-btn ${state.upcomingFilter === 'technical' ? 'active' : ''}" onclick="filterUpcomingEvents('technical', this)">Technical</button>
        <button class="filter-chip-btn ${state.upcomingFilter === 'workshop' ? 'active' : ''}" onclick="filterUpcomingEvents('workshop', this)">Workshops</button>
        <button class="filter-chip-btn ${state.upcomingFilter === 'competition' ? 'active' : ''}" onclick="filterUpcomingEvents('competition', this)">Competitions</button>
        <button class="filter-chip-btn ${state.upcomingFilter === 'cultural' ? 'active' : ''}" onclick="filterUpcomingEvents('cultural', this)">Cultural</button>
      </div>
    </div>
  `;

  html += `<div class="upcoming-section-label">🗓️ Displaying <strong>${filtered.length}</strong> of ${upcoming.length} upcoming events</div>`;

  if (filtered.length === 0) {
    html += `
      <div class="no-upcoming-box">
        <div style="font-size:3rem;margin-bottom:12px;">🔍</div>
        <h3>No Upcoming Events Found</h3>
        <p>No upcoming events matched your filter criteria or search keyword.</p>
        <button class="btn-secondary" style="margin-top: 14px;" onclick="resetUpcomingFilters()">Clear Upcoming Filters</button>
      </div>
    `;
    dom.contentArea.innerHTML = html;
    return;
  }

  html += '<div class="upcoming-grid">';

  filtered.forEach((ev, idx) => {
    const days = daysUntil(ev.date);
    const chipClass = days === 0 ? 'soon' : days <= 7 ? 'soon' : days <= 30 ? 'upcoming' : 'far';
    const chipLabel = days === 0 ? '🔴 Today!' : days === 1 ? '🟠 Tomorrow' : `⏳ ${days} days left`;
    const badgeClass = ev.category.toLowerCase();
    const formattedDate = new Date(ev.date).toLocaleDateString('en-IN', {
      weekday: 'short', day: 'numeric', month: 'short', year: 'numeric'
    });

    html += `
      <div class="upcoming-card" style="animation: cardFadeIn 0.3s ease both; animation-delay: ${idx * 40}ms">
        <div class="upcoming-card-top">
          <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <span class="badge ${badgeClass}">${ev.category}</span>
            <span class="event-fee">${ev.fee}</span>
          </div>
          <span class="countdown-chip ${chipClass}">${chipLabel}</span>
        </div>
        <div class="upcoming-card-body">
          <h3>${ev.title}</h3>
          <p class="event-desc">${ev.description}</p>
          <button class="register-btn" onclick="registerForEvent(${ev.id}, '${ev.title.replace(/'/g, "\\'")}')">
            ✅ Register Now
          </button>
        </div>
        <div class="upcoming-card-footer">
          <span>📅 ${formattedDate} &bull; ${ev.time}</span>
          <span>📍 ${ev.venue}</span>
          <span>👥 ${ev.organizer} &bull; 🎟️ ${ev.seatsAvailable} seats available</span>
        </div>
      </div>
    `;
  });

  html += '</div>';
  dom.contentArea.innerHTML = html;
}

// Global filter handler for upcoming section
window.filterUpcomingEvents = function(chipValue, el) {
  state.upcomingFilter = chipValue;
  renderUpcomingEventsContent();
};

// Global search handler for upcoming section
window.searchUpcomingEvents = function(val) {
  state.upcomingSearch = (val || '').trim();
  renderUpcomingEventsContent();
  const input = document.getElementById("upcomingSearchInput");
  if (input) {
    input.focus();
    input.setSelectionRange(input.value.length, input.value.length);
  }
};

window.resetUpcomingFilters = function() {
  state.upcomingFilter = "all";
  state.upcomingSearch = "";
  renderUpcomingEventsContent();
};

// Interactive event registration handler
window.registerForEvent = function(eventId, title) {
  // Decrement seat count in state
  const evInRaw = (state.rawData || []).find(e => e.id === eventId);
  const evInUpcoming = (state.allUpcomingEvents || []).find(e => e.id === eventId);

  if (evInRaw && evInRaw.seatsAvailable > 0) evInRaw.seatsAvailable -= 1;
  if (evInUpcoming && evInUpcoming.seatsAvailable > 0) evInUpcoming.seatsAvailable -= 1;

  showToast(`🎉 Registration Confirmed for "${title}"! Entry pass sent to your student ERP email.`, 'success');

  // Re-render
  if (state.currentTab === "upcoming") {
    renderUpcomingEventsContent();
  } else if (state.currentTab === "events") {
    renderUpcomingSpotlight();
    applyFiltersAndRender();
  }
};

// Interactive Toast Notification
window.showToast = function(msg, type = "success") {
  const toast = dom.toastNotification || document.getElementById("toastNotification");
  if (!toast) {
    alert(msg);
    return;
  }
  toast.className = `toast-notification show ${type}`;
  toast.innerHTML = `
    <span style="font-size: 1.3rem;">${type === 'success' ? '🎟️' : 'ℹ️'}</span>
    <div><strong>Registration Success</strong><br>${msg}</div>
  `;
  clearTimeout(window.toastTimer);
  window.toastTimer = setTimeout(() => {
    toast.classList.remove("show");
  }, 4500);
};

// Navigation helper to switch to upcoming tab
window.switchToUpcomingTab = function() {
  const btn = document.querySelector('.tab-btn[data-tab="upcoming"]');
  if (btn) {
    dom.tabs.forEach(t => t.classList.remove("active"));
    btn.classList.add("active");
    switchTab("upcoming");
  }
  window.scrollTo({ top: 120, behavior: 'smooth' });
};

// Helper: days from today to a date string
function daysUntil(dateStr) {
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  const target = new Date(dateStr);
  target.setHours(0, 0, 0, 0);
  return Math.round((target - today) / (1000 * 60 * 60 * 24));
}

// Pre-load badge count on page load
async function updateUpcomingBadge() {
  try {
    const result = await ApiService.fetchData("events", true);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    let count = result.data.filter(ev => new Date(ev.date) >= today).length;
    if (count === 0) count = result.data.length;
    const badge = document.getElementById('upcomingBadge');
    if (badge && count > 0) badge.textContent = count;
  } catch (_) {}
}
