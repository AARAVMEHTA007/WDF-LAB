/**
 * Filter & Data Processing Module - Student Hub
 * Handles array filtering, sorting, dependent dropdowns, and pagination.
 */

export const FilterProcessor = {
  /**
   * Filter, sort, and paginate dataset
   */
  process(dataset, rawData, options) {
    const {
      searchTerm = '',
      category = 'all',
      status = 'all',
      country = 'all',
      state = 'all',
      city = 'all',
      sortBy = 'id-asc',
      currentPage = 1,
      pageSize = 6
    } = options;

    let filtered = [...rawData];

    // 1. Search Filter (Array.prototype.filter)
    if (searchTerm.trim() !== '') {
      const query = searchTerm.trim().toLowerCase();
      filtered = filtered.filter(item => {
        if (dataset === 'events') {
          return (
            item.title.toLowerCase().includes(query) ||
            item.description.toLowerCase().includes(query) ||
            item.category.toLowerCase().includes(query) ||
            item.subcategory.toLowerCase().includes(query) ||
            item.venue.toLowerCase().includes(query) ||
            (item.tags && item.tags.some(t => t.toLowerCase().includes(query)))
          );
        } else if (dataset === 'students') {
          return (
            item.name.toLowerCase().includes(query) ||
            item.rollNo.toLowerCase().includes(query) ||
            item.department.toLowerCase().includes(query) ||
            item.email.toLowerCase().includes(query) ||
            (item.skills && item.skills.some(s => s.toLowerCase().includes(query)))
          );
        } else if (dataset === 'faqs') {
          return (
            item.question.toLowerCase().includes(query) ||
            item.answer.toLowerCase().includes(query) ||
            item.category.toLowerCase().includes(query) ||
            item.subcategory.toLowerCase().includes(query)
          );
        }
        return true;
      });
    }

    // 2. Category Filter
    if (category !== 'all') {
      filtered = filtered.filter(item => {
        if (dataset === 'events') return item.category === category;
        if (dataset === 'students') return item.department === category;
        if (dataset === 'faqs') return item.category === category;
        return true;
      });
    }

    // 3. Status Filter
    if (status !== 'all') {
      filtered = filtered.filter(item => {
        if (dataset === 'events') return item.status === status;
        if (dataset === 'students') return item.year === status;
        if (dataset === 'faqs') return item.subcategory === status;
        return true;
      });
    }

    // 4. Intermediate Extension: Dependent Dropdown Filter (Country -> State -> City)
    if (country !== 'all') {
      filtered = filtered.filter(item => item.country === country);
    }
    if (state !== 'all') {
      filtered = filtered.filter(item => item.state === state);
    }
    if (city !== 'all') {
      filtered = filtered.filter(item => item.city === city);
    }

    // 5. Sorting Logic (Array.prototype.sort)
    filtered.sort((a, b) => {
      switch (sortBy) {
        case 'id-asc':
          return a.id - b.id;
        case 'id-desc':
          return b.id - a.id;
        case 'title-asc':
          const titleA = a.title || a.name || a.question;
          const titleB = b.title || b.name || b.question;
          return titleA.localeCompare(titleB);
        case 'title-desc':
          const titleA_d = a.title || a.name || a.question;
          const titleB_d = b.title || b.name || b.question;
          return titleB_d.localeCompare(titleA_d);
        case 'metric-desc':
          const valA = a.registered || a.gpa || a.views || 0;
          const valB = b.registered || b.gpa || b.views || 0;
          return valB - valA;
        case 'metric-asc':
          const valA_asc = a.registered || a.gpa || a.views || 0;
          const valB_asc = b.registered || b.gpa || b.views || 0;
          return valA_asc - valB_asc;
        case 'date-desc':
          const dateA = new Date(a.date || a.lastUpdated || '2026-01-01');
          const dateB = new Date(b.date || b.lastUpdated || '2026-01-01');
          return dateB - dateA;
        case 'date-asc':
          const dateA_a = new Date(a.date || a.lastUpdated || '2026-01-01');
          const dateB_a = new Date(b.date || b.lastUpdated || '2026-01-01');
          return dateA_a - dateB_a;
        default:
          return a.id - b.id;
      }
    });

    // 6. Pagination Math Logic
    const totalRecords = filtered.length;
    const totalPages = Math.ceil(totalRecords / pageSize) || 1;
    const validatedPage = Math.min(Math.max(1, currentPage), totalPages);

    const startIndex = (validatedPage - 1) * pageSize;
    const endIndex = Math.min(startIndex + pageSize, totalRecords);

    // Array.prototype.slice for pagination
    const paginatedItems = filtered.slice(startIndex, endIndex);

    return {
      items: paginatedItems,
      totalRecords,
      totalPages,
      currentPage: validatedPage,
      startIndex: totalRecords > 0 ? startIndex + 1 : 0,
      endIndex,
      pageSize
    };
  },

  /**
   * Helper for Dependent Dropdowns: Extracts unique options given parent selections
   */
  getDependentOptions(rawData, selectedCountry = 'all', selectedState = 'all') {
    // Unique countries
    const countries = [...new Set(rawData.map(item => item.country).filter(Boolean))].sort();

    // Unique states based on selected country
    let stateItems = rawData;
    if (selectedCountry !== 'all') {
      stateItems = stateItems.filter(item => item.country === selectedCountry);
    }
    const states = [...new Set(stateItems.map(item => item.state).filter(Boolean))].sort();

    // Unique cities based on selected country and state
    let cityItems = stateItems;
    if (selectedState !== 'all') {
      cityItems = cityItems.filter(item => item.state === selectedState);
    }
    const cities = [...new Set(cityItems.map(item => item.city).filter(Boolean))].sort();

    return { countries, states, cities };
  },

  /**
   * Extract unique categories and statuses for dropdown filters
   */
  getFilterCategories(dataset, rawData) {
    let categories = [];
    let statuses = [];

    if (dataset === 'events') {
      categories = [...new Set(rawData.map(item => item.category))].sort();
      statuses = [...new Set(rawData.map(item => item.status))].sort();
    } else if (dataset === 'students') {
      categories = [...new Set(rawData.map(item => item.department))].sort();
      statuses = [...new Set(rawData.map(item => item.year))].sort();
    } else if (dataset === 'faqs') {
      categories = [...new Set(rawData.map(item => item.category))].sort();
      statuses = [...new Set(rawData.map(item => item.subcategory))].sort();
    }

    return { categories, statuses };
  }
};
