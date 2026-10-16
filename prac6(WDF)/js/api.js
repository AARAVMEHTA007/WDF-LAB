/**
 * api.js - Data Fetching & Caching Layer for Practical 6
 * Author: Aarav Mehta
 * 
 * Responsibilities:
 * 1. Fetch external JSON datasets using the Fetch API (ES6 async/await).
 * 2. Handle loading, error, and HTTP status codes (response.ok).
 * 3. Advanced Extension: Cache responses in localStorage for offline-like display.
 * 4. Fallback recovery for direct file:// protocol browsing.
 */

const ApiService = {
  // Base paths for our datasets
  endpoints: {
    events: "data/events.json",
    students: "data/students.json",
    notices: "data/notices.json",
    faqs: "data/faqs.json"
  },

  // Cache configuration (Advanced Extension)
  CACHE_PREFIX: "prac6_cache_v3_",
  CACHE_TIME_PREFIX: "prac6_time_v3_",

  /**
   * Fetch data for a specific dataset key ('events' | 'students' | 'faqs')
   * @param {string} datasetKey
   * @param {boolean} forceRefresh - If true, bypasses reading from cache first
   * @returns {Promise<{ data: Array, source: 'network'|'cache'|'fallback', timestamp: string }>}
   */
  async fetchData(datasetKey, forceRefresh = false) {
    const url = this.endpoints[datasetKey];
    if (!url) {
      throw new Error(`Unknown dataset key: "${datasetKey}"`);
    }

    const cacheKey = this.CACHE_PREFIX + datasetKey;
    const cacheTimeKey = this.CACHE_TIME_PREFIX + datasetKey;

    // If offline or not forcing refresh, check if we have cached data first
    if (!navigator.onLine && !forceRefresh) {
      const cached = localStorage.getItem(cacheKey);
      if (cached) {
        console.log(`[Offline Mode] Loaded ${datasetKey} from localStorage cache.`);
        return {
          data: JSON.parse(cached),
          source: "cache",
          timestamp: localStorage.getItem(cacheTimeKey) || "Previous session"
        };
      }
    }

    try {
      console.log(`[Fetch API] Requesting ${url}...`);
      const response = await fetch(url, {
        cache: forceRefresh ? "reload" : "default"
      });

      // Check HTTP status code
      if (!response.ok) {
        throw new Error(`HTTP Error: Failed to fetch data (${response.status} ${response.statusText})`);
      }

      // Parse JSON
      const data = await response.json();

      // Advanced Extension: Save to localStorage for offline resilience
      const nowStr = new Date().toLocaleTimeString();
      try {
        localStorage.setItem(cacheKey, JSON.stringify(data));
        localStorage.setItem(cacheTimeKey, nowStr);
      } catch (storageErr) {
        console.warn("Could not save to localStorage (quota or disabled):", storageErr);
      }

      return {
        data: data,
        source: "network",
        timestamp: nowStr
      };

    } catch (error) {
      console.warn(`[Fetch Warning] Network request failed for ${url}:`, error.message);

      // Attempt reading from localStorage cache if network fails
      const cached = localStorage.getItem(cacheKey);
      if (cached) {
        console.log(`[Cache Fallback] Returning cached data for ${datasetKey}.`);
        return {
          data: JSON.parse(cached),
          source: "cache",
          timestamp: localStorage.getItem(cacheTimeKey) || "Previous session"
        };
      }

      // If running directly via file:// protocol and CORS blocked local fetch
      if (window.location.protocol === "file:") {
        console.warn("[Notice] Running from file:// protocol. Local fetch may be restricted by browser CORS.");
        const fallbackData = this.getEmbeddedFallback(datasetKey);
        if (fallbackData) {
          return {
            data: fallbackData,
            source: "fallback",
            timestamp: "Embedded Local Fallback"
          };
        }
      }

      // Re-throw so caller can display friendly error state
      throw error;
    }
  },

  /**
   * Clear cached records from localStorage
   */
  clearCache(datasetKey) {
    if (datasetKey) {
      localStorage.removeItem(this.CACHE_PREFIX + datasetKey);
      localStorage.removeItem(this.CACHE_TIME_PREFIX + datasetKey);
    } else {
      Object.keys(this.endpoints).forEach(key => {
        localStorage.removeItem(this.CACHE_PREFIX + key);
        localStorage.removeItem(this.CACHE_TIME_PREFIX + key);
      });
    }
  },

  /**
   * Check if a dataset has cached data
   */
  hasCachedData(datasetKey) {
    return Boolean(localStorage.getItem(this.CACHE_PREFIX + datasetKey));
  },

  /**
   * Fallback data in case user opens index.html directly with file:/// without a local server
   */
  getEmbeddedFallback(key) {
    if (key === "events") {
      return [
        { id: 1,  title: "Web Development Hackathon 2026",         category: "Technical",    date: "2026-10-18", time: "09:00 AM - 05:00 PM", venue: "Lab 401 & 402, IT Block",      organizer: "Web Development Club",       fee: "Free",  seatsAvailable: 12,  description: "A 24-hour hackathon where participants build modern, responsive web applications using frontend and backend technologies." },
        { id: 2,  title: "AI & Machine Learning BootCamp",         category: "Workshop",     date: "2026-10-22", time: "10:00 AM - 04:00 PM", venue: "Seminar Hall B",               organizer: "Data Science Department",     fee: "₹150", seatsAvailable: 5,   description: "Hands-on workshop exploring supervised learning, neural network basics, and model evaluation in Python." },
        { id: 3,  title: "Campus Placement Preparation Talk",       category: "Career",       date: "2026-10-25", time: "11:00 AM - 01:00 PM", venue: "Main Auditorium",              organizer: "Training & Placement Cell",  fee: "Free",  seatsAvailable: 45,  description: "Interactive talk by alumni placed at top tech firms covering resume building, coding rounds, and HR interview tips." },
        { id: 4,  title: "Speed Coding Competition",                category: "Competition",  date: "2026-10-28", time: "02:00 PM - 04:30 PM", venue: "Computer Center 1",            organizer: "Coding Club",                fee: "₹50",  seatsAvailable: 8,   description: "Timed problem-solving tournament focusing on data structures, algorithms, and logic optimization." },
        { id: 5,  title: "Annual Cultural Evening - Spandan",       category: "Cultural",     date: "2026-11-02", time: "05:00 PM - 09:30 PM", venue: "Open Air Amphitheatre",        organizer: "Student Council",            fee: "Free",  seatsAvailable: 120, description: "Celebration of student talent with music, dance, theatrical drama, and art exhibitions." },
        { id: 6,  title: "Cybersecurity & Ethical Hacking Basics",  category: "Workshop",     date: "2026-11-06", time: "10:30 AM - 03:30 PM", venue: "Lab 205, CE Block",           organizer: "Cyber Cell",                 fee: "₹200", seatsAvailable: 18,  description: "Introduction to network vulnerability assessment, packet sniffing, and secure coding practices." },
        { id: 7,  title: "Open Source Contribution Drive",          category: "Technical",    date: "2026-11-10", time: "01:00 PM - 04:00 PM", venue: "Lab 103, MCA Wing",           organizer: "FOSS Community",             fee: "Free",  seatsAvailable: 22,  description: "Guide to Git, GitHub workflows, pull requests, and making your first open source contribution." },
        { id: 8,  title: "UI/UX Design Sprint with Figma",          category: "Workshop",     date: "2026-11-14", time: "11:00 AM - 02:30 PM", venue: "Multimedia Lab",              organizer: "Design Circle",              fee: "₹100", seatsAvailable: 10,  description: "Learn user flow mapping, wireframing, component systems, and prototyping using Figma." },
        { id: 9,  title: "Inter-College Robotics Expo",             category: "Competition",  date: "2026-11-18", time: "09:30 AM - 04:30 PM", venue: "Indoor Sports Complex",        organizer: "Robotics Club",              fee: "₹250", seatsAvailable: 15,  description: "Exhibition of line followers, obstacle avoiders, and combat bots designed by engineering teams." },
        { id: 10, title: "Cloud Computing on AWS & Azure",          category: "Technical",    date: "2026-11-22", time: "02:00 PM - 05:00 PM", venue: "Seminar Hall A",              organizer: "IT Department",              fee: "Free",  seatsAvailable: 30,  description: "Overview of virtualization, serverless compute, storage buckets, and deployment pipelines." },
        { id: 11, title: "Technical Paper Presentation Contest",    category: "Competition",  date: "2026-11-25", time: "10:00 AM - 01:30 PM", venue: "Conference Room 1",           organizer: "IEEE Student Branch",        fee: "₹100", seatsAvailable: 14,  description: "Research paper presentation on emerging technologies, reviewed by faculty judges and industry guests." },
        { id: 12, title: "Higher Education & GRE/GATE Roadmap",    category: "Career",       date: "2026-11-28", time: "03:00 PM - 05:00 PM", venue: "Room 301, Academic Block",    organizer: "Career Guidance Cell",       fee: "Free",  seatsAvailable: 50,  description: "Guidance on preparing for competitive exams, choosing universities, and drafting statement of purpose." },
        { id: 13, title: "Photography and Short Film Fest",         category: "Cultural",     date: "2026-12-02", time: "04:00 PM - 07:00 PM", venue: "Media Studio",                organizer: "Creative Arts Guild",        fee: "Free",  seatsAvailable: 35,  description: "Screening of creative short films and photo exhibition on the theme 'Life Around the Campus'." },
        { id: 14, title: "Mobile App Development with Flutter",     category: "Workshop",     date: "2026-12-05", time: "10:00 AM - 03:00 PM", venue: "Lab 405",                    organizer: "App Dev Collective",         fee: "₹150", seatsAvailable: 16,  description: "Build cross-platform applications for iOS and Android using Dart, state management, and API calls." },
        { id: 15, title: "Entrepreneurship & Startup Pitch Day",    category: "Career",       date: "2026-12-09", time: "10:30 AM - 02:00 PM", venue: "Incubation Center Hall",      organizer: "E-Cell",                     fee: "Free",  seatsAvailable: 25,  description: "Student entrepreneurs pitch innovative business concepts before angel investors and mentors." },
        { id: 16, title: "Database Systems & SQL Masterclass",      category: "Technical",    date: "2026-12-12", time: "01:30 PM - 04:30 PM", venue: "Lab 202, CE Wing",           organizer: "Database Research Group",    fee: "Free",  seatsAvailable: 20,  description: "Practical query optimization, indexing strategies, and normalization in relational databases." },
        { id: 17, title: "Blockchain & Web3 Development Workshop",  category: "Workshop",     date: "2026-12-16", time: "10:00 AM - 04:00 PM", venue: "Lab 303, IT Block",           organizer: "Blockchain Enthusiasts Club",fee: "₹200", seatsAvailable: 20,  description: "Deep dive into smart contracts, Solidity programming, DeFi principles, and deploying on Ethereum testnets." },
        { id: 18, title: "National Level Quiz Bowl — Science & Tech",category: "Competition",  date: "2026-12-20", time: "09:00 AM - 12:00 PM", venue: "Main Auditorium",             organizer: "Science Forum",              fee: "₹30",  seatsAvailable: 60,  description: "Fast-paced science and technology quiz with buzzer rounds, rapid-fire segments, and cash prizes for winners." },
        { id: 19, title: "Year-End Freshers & Farewell Gala",       category: "Cultural",     date: "2026-12-26", time: "06:00 PM - 10:00 PM", venue: "Grand Banquet Hall",           organizer: "Student Council",            fee: "Free",  seatsAvailable: 200, description: "Combined freshers welcome and final-year farewell celebration with awards, performances, and recognition ceremony." },
        { id: 20, title: "DevOps & CI/CD Pipeline Masterclass",     category: "Technical",    date: "2026-12-30", time: "10:00 AM - 03:30 PM", venue: "Lab 501, IT Block",           organizer: "Cloud & DevOps Society",     fee: "Free",  seatsAvailable: 24,  description: "Hands-on training in Jenkins, Docker, GitHub Actions, and Kubernetes for building robust CI/CD pipelines." }
      ];
    }
    if (key === "students") {
      return [
        { id: 1, rollNo: "24DCE001", name: "Aarav Mehta", department: "Computer Engineering", semester: 5, gpa: 9.24, email: "aarav.ce@charusat.edu.in", country: "India", state: "Gujarat", city: "Ahmedabad", skills: ["JavaScript", "HTML/CSS", "React"], status: "Active" },
        { id: 2, rollNo: "24DCE002", name: "Diya Patel", department: "Computer Engineering", semester: 5, gpa: 8.95, email: "diya.patel@charusat.edu.in", country: "India", state: "Gujarat", city: "Vadodara", skills: ["Python", "Machine Learning"], status: "Active" },
        { id: 3, rollNo: "24DCE004", name: "Ananya Iyer", department: "Computer Engineering", semester: 7, gpa: 9.60, email: "ananya.iyer@charusat.edu.in", country: "India", state: "Maharashtra", city: "Mumbai", skills: ["Java", "Spring Boot", "AWS"], status: "Active" }
      ];
    }
    if (key === "notices") {
      return [
        { id: 1, title: "End-Semester Winter Examination 2026 Schedule", category: "Examinations", date: "2026-10-14", priority: "Urgent", issuedBy: "Controller of Examinations", targetAudience: "All B.Tech & MCA Students", description: "The final timetable for the End-Semester Theory and Practical Examinations has been published. Hall tickets will be available on the student ERP portal from October 20." },
        { id: 2, title: "Campus Recruitment Drive: Tata Consultancy Services (TCS)", category: "Placements", date: "2026-10-12", priority: "Important", issuedBy: "Training & Placement Cell", targetAudience: "B.Tech CE/IT Final Year (2027 Batch)", description: "TCS Digital and Ninja recruitment drive registrations are now live. Eligible candidates having CGPA >= 6.5 must register before October 18." },
        { id: 3, title: "Practical Lab Journal & Term Work Submission Deadline", category: "Academic", date: "2026-10-10", priority: "Urgent", issuedBy: "Department of Computer Engineering", targetAudience: "3rd & 5th Semester Students", description: "All enrolled students must get their continuous evaluation journals signed and verified by respective lab in-charges before the term end date." }
      ];
    }
    if (key === "faqs") {
      return [
        { id: 1, question: "How do I register for departmental technical events?", category: "Events", answer: "Register through the student portal under the Events section.", updated: "2026-09-15" },
        { id: 2, question: "What is the minimum attendance requirement?", category: "Academics", answer: "Students must maintain at least 75% overall attendance.", updated: "2026-09-18" }
      ];
    }
    return null;
  }
};
