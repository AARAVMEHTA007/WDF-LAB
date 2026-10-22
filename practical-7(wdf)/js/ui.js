/**
 * UI Render Module - Student Hub (Practical 6)
 * Handles rendering HTML cards, pagination controls, search highlights, and modals.
 * Clean, human-made UI without fancy stock photos or AI-generated image elements.
 */

export const UI = {
  /**
   * Escape HTML to prevent XSS
   */
  escapeHtml(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  },

  /**
   * Highlight matching text during search
   */
  highlightText(text, searchTerm) {
    if (!text) return '';
    const safeText = this.escapeHtml(text);
    if (!searchTerm || !searchTerm.trim()) return safeText;

    const term = searchTerm.trim().replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const regex = new RegExp(`(${term})`, 'gi');
    return safeText.replace(regex, '<mark class="search-highlight">$1</mark>');
  },

  /**
   * Extract initials from a person's name for a clean profile circle badge
   */
  getInitials(name) {
    if (!name) return 'ST';
    const parts = name.trim().split(/\s+/);
    if (parts.length >= 2) {
      return (parts[0][0] + parts[1][0]).toUpperCase();
    }
    return parts[0].slice(0, 2).toUpperCase();
  },

  /**
   * Generate a stable, pleasant background color for student initials
   */
  getAvatarColor(name) {
    const palette = ['#2563eb', '#059669', '#7c3aed', '#d97706', '#0284c7', '#dc2626', '#0d9488', '#4f46e5'];
    let hash = 0;
    for (let i = 0; i < (name || '').length; i++) {
      hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return palette[Math.abs(hash) % palette.length];
  },

  /**
   * Render Loading State
   */
  renderLoading(containerEl) {
    containerEl.innerHTML = `
      <div class="loading-container" role="status">
        <div class="simple-spinner"></div>
        <p class="loading-text">Loading records, please wait...</p>
      </div>
    `;
  },

  /**
   * Render Error State
   */
  renderError(containerEl, message, onRetry) {
    containerEl.innerHTML = `
      <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h3>Unable to Load Data</h3>
        <p class="error-message">${this.escapeHtml(message)}</p>
        <div class="error-actions">
          <button class="btn btn-primary" id="btn-retry-fetch">Try Again</button>
          <button class="btn btn-secondary" id="btn-load-cache">Load Cached Data</button>
        </div>
      </div>
    `;

    const retryBtn = containerEl.querySelector('#btn-retry-fetch');
    if (retryBtn && onRetry) {
      retryBtn.addEventListener('click', () => onRetry(false));
    }
    const cacheBtn = containerEl.querySelector('#btn-load-cache');
    if (cacheBtn && onRetry) {
      cacheBtn.addEventListener('click', () => onRetry(true));
    }
  },

  /**
   * Render Empty / No Results State
   */
  renderEmpty(containerEl, searchTerm) {
    containerEl.innerHTML = `
      <div class="empty-container">
        <div class="empty-icon">🔍</div>
        <h3>No Matching Records Found</h3>
        <p>No results matched your search query "${this.escapeHtml(searchTerm)}" or selected filter criteria.</p>
        <div style="margin-top: 14px;">
          <button class="btn btn-secondary" id="btn-clear-filters">Reset All Filters</button>
        </div>
      </div>
    `;

    const clearBtn = containerEl.querySelector('#btn-clear-filters');
    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        const resetBtn = document.getElementById('btn-reset');
        if (resetBtn) resetBtn.click();
      });
    }
  },

  /**
   * Main Render Method for Dataset Cards
   */
  renderDataGrid(containerEl, dataset, items, searchTerm, onViewDetail) {
    if (!items || items.length === 0) {
      this.renderEmpty(containerEl, searchTerm);
      return;
    }

    const grid = document.createElement('div');
    grid.className = `data-grid data-grid-${dataset}`;

    items.forEach(item => {
      const card = document.createElement('article');
      card.className = 'data-card';
      card.dataset.id = item.id;

      if (dataset === 'events') {
        card.innerHTML = this.createEventCardHtml(item, searchTerm);
      } else if (dataset === 'students') {
        card.innerHTML = this.createStudentCardHtml(item, searchTerm);
      } else if (dataset === 'faqs') {
        card.innerHTML = this.createFaqCardHtml(item, searchTerm);
      }

      // Add click handler for detail view modal
      card.addEventListener('click', () => {
        if (onViewDetail) onViewDetail(item);
      });

      grid.appendChild(card);
    });

    containerEl.innerHTML = '';
    containerEl.appendChild(grid);
  },

  /**
   * HTML Template for Event Card (Clean & Simple, No Fancy Photos)
   */
  createEventCardHtml(item, searchTerm) {
    const title = this.highlightText(item.title, searchTerm);
    const desc = this.highlightText(item.description, searchTerm);
    const category = this.escapeHtml(item.category);
    const dateStr = new Date(item.date).toLocaleDateString('en-US', {
      month: 'short', day: 'numeric', year: 'numeric'
    });
    const percent = Math.min(100, Math.round((item.registered / item.capacity) * 100));

    return `
      <div class="card-header-simple">
        <span class="card-badge-id">Event #${item.id}</span>
        <span class="card-badge-category category-${item.category.toLowerCase().replace(/[^a-z0-9]/g, '')}">${category}</span>
        <span class="card-badge-status status-${item.status.toLowerCase()}">${this.escapeHtml(item.status)}</span>
      </div>
      <div class="card-body">
        <h3 class="card-title">${title}</h3>
        <p class="card-desc">${desc}</p>

        <div class="card-meta">
          <div class="meta-row">
            <span><strong>Date:</strong> ${dateStr}</span>
            <span><strong>Time:</strong> ${this.escapeHtml(item.time)}</span>
          </div>
          <div class="meta-row">
            <span><strong>Venue:</strong> ${this.highlightText(item.venue, searchTerm)}</span>
          </div>
          <div class="meta-row">
            <span><strong>Location:</strong> ${this.escapeHtml(item.city)}, ${this.escapeHtml(item.state)}</span>
          </div>
        </div>

        <div class="capacity-bar-container" title="${item.registered} / ${item.capacity} seats filled">
          <div class="capacity-info">
            <span>Registration: ${item.registered} / ${item.capacity}</span>
            <span>${percent}%</span>
          </div>
          <div class="progress-bar">
            <div class="progress-fill ${percent >= 90 ? 'danger' : ''}" style="width: ${percent}%"></div>
          </div>
        </div>

        <div class="card-tags">
          ${item.tags ? item.tags.map(t => `<span class="tag-pill">#${this.escapeHtml(t)}</span>`).join('') : ''}
        </div>
      </div>
      <div class="card-footer">
        <span class="organizer-text" title="${this.escapeHtml(item.organizer)}">Org: ${this.escapeHtml(item.organizer)}</span>
        <button class="btn btn-sm btn-action">View Details</button>
      </div>
    `;
  },

  /**
   * HTML Template for Student Profile Card (Clean Initials Circle, No Cartoon Avatars)
   */
  createStudentCardHtml(item, searchTerm) {
    const name = this.highlightText(item.name, searchTerm);
    const rollNo = this.highlightText(item.rollNo, searchTerm);
    const dept = this.highlightText(item.department, searchTerm);
    const initials = this.getInitials(item.name);
    const avatarColor = this.getAvatarColor(item.name);

    return `
      <div class="student-card-header">
        <div class="student-avatar-initials" style="background-color: ${avatarColor};">${initials}</div>
        <div class="student-header-text">
          <h3 class="card-title">${name}</h3>
          <span class="student-roll">Roll No: <strong>${rollNo}</strong> • ID #${item.id}</span>
          <span class="card-badge-category category-dept">${dept}</span>
        </div>
      </div>
      <div class="card-body">
        <div class="card-meta">
          <div class="meta-row">
            <span><strong>Year:</strong> ${this.escapeHtml(item.year)}</span>
            <span><strong>GPA:</strong> <span class="gpa-badge">${item.gpa} / 10.0</span></span>
          </div>
          <div class="meta-row">
            <span><strong>Email:</strong> ${this.highlightText(item.email, searchTerm)}</span>
          </div>
          <div class="meta-row">
            <span><strong>Location:</strong> ${this.escapeHtml(item.city)}, ${this.escapeHtml(item.state)}, ${this.escapeHtml(item.country)}</span>
          </div>
        </div>

        <div class="student-skills">
          <div class="skills-label">Skills & Tech Stack:</div>
          <div class="card-tags">
            ${item.skills ? item.skills.map(s => `<span class="skill-pill">${this.highlightText(s, searchTerm)}</span>`).join('') : ''}
          </div>
        </div>
      </div>
      <div class="card-footer">
        <span class="status-indicator active">● Active Student</span>
        <button class="btn btn-sm btn-action">View Profile</button>
      </div>
    `;
  },

  /**
   * HTML Template for FAQ Card (Clean Academic FAQ Layout)
   */
  createFaqCardHtml(item, searchTerm) {
    const question = this.highlightText(item.question, searchTerm);
    const answer = this.highlightText(item.answer, searchTerm);
    const category = this.highlightText(item.category, searchTerm);

    return `
      <div class="card-header-simple">
        <span class="card-badge-id">FAQ #${item.id}</span>
        <span class="card-badge-category">${category}</span>
        <span class="faq-subtag">${this.escapeHtml(item.subcategory)}</span>
      </div>
      <div class="card-body">
        <h3 class="card-title faq-title">${question}</h3>
        <p class="card-desc faq-answer">${answer}</p>
        <div class="card-meta">
          <div class="meta-row">
            <span><strong>Region:</strong> ${this.escapeHtml(item.city)}, ${this.escapeHtml(item.country)}</span>
            <span><strong>Updated:</strong> ${this.escapeHtml(item.lastUpdated)}</span>
          </div>
        </div>
      </div>
      <div class="card-footer faq-footer">
        <span class="faq-stats">${item.views} Views • ${item.helpfulCount} Helpful</span>
        <button class="btn btn-sm btn-action">Read FAQ</button>
      </div>
    `;
  },

  /**
   * Render Pagination Control Buttons
   */
  renderPagination(containerEl, { currentPage, totalPages, totalRecords, startIndex, endIndex }, onPageChange) {
    if (totalRecords === 0) {
      containerEl.innerHTML = '';
      return;
    }

    let buttonsHtml = '';

    // First & Prev Buttons
    const prevDisabled = currentPage === 1 ? 'disabled' : '';
    buttonsHtml += `<button class="page-btn page-nav" id="p-first" ${prevDisabled} title="First Page">First</button>`;
    buttonsHtml += `<button class="page-btn page-nav" id="p-prev" ${prevDisabled} title="Previous Page">Prev</button>`;

    // Numeric Page Buttons
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);

    if (endPage - startPage < 4) {
      startPage = Math.max(1, endPage - 4);
    }

    for (let p = startPage; p <= endPage; p++) {
      const activeClass = p === currentPage ? 'active' : '';
      buttonsHtml += `<button class="page-btn page-num ${activeClass}" data-page="${p}">${p}</button>`;
    }

    // Next & Last Buttons
    const nextDisabled = currentPage === totalPages ? 'disabled' : '';
    buttonsHtml += `<button class="page-btn page-nav" id="p-next" ${nextDisabled} title="Next Page">Next</button>`;
    buttonsHtml += `<button class="page-btn page-nav" id="p-last" ${nextDisabled} title="Last Page">Last</button>`;

    containerEl.innerHTML = `
      <div class="pagination-info">
        Showing <strong>${startIndex}–${endIndex}</strong> of <strong>${totalRecords}</strong> records (Page <strong>${currentPage}</strong> of <strong>${totalPages}</strong>)
      </div>
      <div class="pagination-controls">
        ${buttonsHtml}
      </div>
    `;

    // Attach click listeners
    const navFirst = containerEl.querySelector('#p-first');
    if (navFirst) navFirst.onclick = () => onPageChange(1);

    const navPrev = containerEl.querySelector('#p-prev');
    if (navPrev) navPrev.onclick = () => onPageChange(currentPage - 1);

    const navNext = containerEl.querySelector('#p-next');
    if (navNext) navNext.onclick = () => onPageChange(currentPage + 1);

    const navLast = containerEl.querySelector('#p-last');
    if (navLast) navLast.onclick = () => onPageChange(totalPages);

    containerEl.querySelectorAll('.page-num').forEach(btn => {
      btn.onclick = () => onPageChange(Number(btn.dataset.page));
    });
  },

  /**
   * Render Modal Item Detail
   */
  showModal(item, dataset) {
    const modalOverlay = document.getElementById('modal-overlay');
    const modalContent = document.getElementById('modal-body-content');

    let html = `<h2>${this.escapeHtml(item.title || item.name || item.question)}</h2>`;
    html += `<p class="modal-subtitle">Dataset: ${dataset.toUpperCase()} • Record ID: #${item.id}</p>`;

    html += `<table class="detail-table">`;
    Object.entries(item).forEach(([key, val]) => {
      // Exclude unnecessary image fields
      if (key === 'image' || key === 'avatar') return;
      let displayVal = val;
      if (Array.isArray(val)) displayVal = val.join(', ');
      if (typeof val === 'object' && val !== null) displayVal = JSON.stringify(val);
      html += `
        <tr>
          <th>${this.escapeHtml(key)}</th>
          <td>${this.escapeHtml(displayVal)}</td>
        </tr>
      `;
    });
    html += `</table>`;

    modalContent.innerHTML = html;
    modalOverlay.classList.add('active');
  },

  /**
   * Hide Modal
   */
  hideModal() {
    const modalOverlay = document.getElementById('modal-overlay');
    if (modalOverlay) modalOverlay.classList.remove('active');
  },

  /**
   * Display Toast Notification
   */
  showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;

    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${message}</span>`;

    toastContainer.appendChild(toast);
    setTimeout(() => {
      toast.classList.add('fade-out');
      setTimeout(() => toast.remove(), 350);
    }, 2500);
  }
};
