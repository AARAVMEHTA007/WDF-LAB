

const CACHE_PREFIX = 'student_hub_cache_';

export const Api = {

  async fetchData(datasetName, simulateError = false, delayMs = 300) {
    if (delayMs > 0) {
      await new Promise(resolve => setTimeout(resolve, delayMs));
    }

    if (simulateError) {
      throw new Error(`Failed to load '${datasetName}.json'. Please try again.`);
    }

    const filePath = `./data/${datasetName}.json`;

    try {
      const response = await fetch(filePath);

      if (!response.ok) {
        throw new Error(`Error loading file: ${filePath}`);
      }

      const data = await response.json();

      // Save cache in localStorage
      try {
        localStorage.setItem(CACHE_PREFIX + datasetName, JSON.stringify({
          timestamp: new Date().toISOString(),
          payload: data
        }));
      } catch (e) {
        console.warn('LocalStorage unavailable:', e);
      }

      return { data, isFromCache: false };

    } catch (error) {
      console.error(`Fetch Error:`, error);

      const cached = localStorage.getItem(CACHE_PREFIX + datasetName);
      if (cached) {
        const parsed = JSON.parse(cached);
        return {
          data: parsed.payload,
          isFromCache: true,
          cacheTime: parsed.timestamp,
          error: error.message
        };
      }

      throw error;
    }
  },

  getCacheInfo(datasetName) {
    const cached = localStorage.getItem(CACHE_PREFIX + datasetName);
    return cached ? JSON.parse(cached) : null;
  },

  clearCache() {
    Object.keys(localStorage).forEach(key => {
      if (key.startsWith(CACHE_PREFIX)) {
        localStorage.removeItem(key);
      }
    });
  }
};
