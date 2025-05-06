const CONFIG = {
    INPUT_ID: 'tournament_location',
    INPUT_NAME: 'tournament[tournamentLocation]',
    MAP_ID: 'map',
    NOMINATIM_URL: 'https://nominatim.openstreetmap.org/search',
    NOMINATIM_REVERSE_URL: 'https://nominatim.openstreetmap.org/reverse',
    USER_AGENT: 'SPIN-Sports-App (contact@yourapp.com)', // Replace with your contact email
    DEBOUNCE_DELAY: 500, // ms
    BLUR_DELAY: 300, // ms
    MIN_QUERY_LENGTH: 2,
    MAP_HEIGHT: '500px',
    Z_INDEX: '1000',
    SUGGESTIONS_LIMIT: 5
};

class TournamentLocationAutocomplete {
    constructor() {
        this.input = null;
        this.map = null;
        this.marker = null;
        this.isMapInitialized = false;
        this.suggestionsContainer = null;
        this.cache = new Map();
    }

    initialize() {
        this.setupInput();
        if (!this.input) return;
        this.setupSuggestionsContainer();
        this.setupMap();
        this.addEventListeners();
    }

    setupInput() {
        this.input = document.getElementById(CONFIG.INPUT_ID);
        if (!this.input) {
            this.input = document.querySelector(`input[name="${CONFIG.INPUT_NAME}"]`);
            if (this.input) {
                console.debug(`Found input with name="${CONFIG.INPUT_NAME}", ID: ${this.input.id}`);
            } else {
                console.error(`Input element not found for #${CONFIG.INPUT_ID} or name="${CONFIG.INPUT_NAME}". Autocomplete disabled.`);
                return;
            }
        } else {
            console.debug(`Found input with ID="${CONFIG.INPUT_ID}"`);
        }
        this.input.setAttribute('aria-autocomplete', 'list');
    }

    setupSuggestionsContainer() {
        this.suggestionsContainer = document.createElement('div');
        this.suggestionsContainer.className = 'list-group position-absolute w-100';
        this.suggestionsContainer.style.zIndex = CONFIG.Z_INDEX;
        this.suggestionsContainer.setAttribute('role', 'listbox');
        this.input.parentNode.appendChild(this.suggestionsContainer);
    }

    setupMap() {
        const mapDiv = document.getElementById(CONFIG.MAP_ID);
        if (mapDiv && typeof L !== 'undefined') {
            try {
                mapDiv.style.height = CONFIG.MAP_HEIGHT;
                this.map = L.map(CONFIG.MAP_ID).setView([51.505, -0.09], 13); // Default to London; consider geolocation
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(this.map);
                this.isMapInitialized = true;
                console.debug('Leaflet map initialized successfully');
            } catch (error) {
                console.error('Error initializing Leaflet map:', error);
            }
        } else {
            console.error(`Map element #${CONFIG.MAP_ID} not found or Leaflet not loaded. Map functionality disabled.`);
        }
    }

    debounce(func, wait) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    sanitizeText(text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    }

    async fetchSuggestions(query) {
        if (query.length < CONFIG.MIN_QUERY_LENGTH) {
            this.suggestionsContainer.innerHTML = '';
            return;
        }

        if (this.cache.has(query)) {
            this.renderSuggestions(this.cache.get(query));
            return;
        }

        try {
            const response = await fetch(
                `${CONFIG.NOMINATIM_URL}?format=json&q=${encodeURIComponent(query)}&addressdetails=1&limit=${CONFIG.SUGGESTIONS_LIMIT}`,
                {
                    headers: { 'User-Agent': CONFIG.USER_AGENT }
                }
            );
            if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
            const results = await response.json();
            this.cache.set(query, results);
            this.renderSuggestions(results);
        } catch (error) {
            console.error('Error fetching suggestions:', error);
            this.suggestionsContainer.innerHTML = '<div class="list-group-item text-danger">Error fetching suggestions</div>';
        }
    }

    renderSuggestions(results) {
        this.suggestionsContainer.innerHTML = '';
        if (results.length === 0) {
            this.suggestionsContainer.innerHTML = '<div class="list-group-item">No results found</div>';
            return;
        }

        results.forEach((result, index) => {
            const item = document.createElement('div');
            item.className = 'list-group-item list-group-item-action';
            item.style.cursor = 'pointer';
            item.textContent = this.sanitizeText(result.display_name);
            item.setAttribute('role', 'option');
            item.setAttribute('id', `suggestion-${index}`);
            item.addEventListener('mousedown', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.selectSuggestion(result);
            });
            this.suggestionsContainer.appendChild(item);
        });
    }

    selectSuggestion(result) {
        this.input.value = this.sanitizeText(result.display_name);
        this.input.classList.remove('is-invalid');
        this.input.setAttribute('aria-invalid', 'false');
        this.suggestionsContainer.innerHTML = '';
        if (this.isMapInitialized && result.lat && result.lon) {
            this.updateMarker(result.lat, result.lon);
        }
    }

    async handleMapClick(e) {
        if (!this.isMapInitialized) return;
        const { lat, lng } = e.latlng;
        try {
            const response = await fetch(
                `${CONFIG.NOMINATIM_REVERSE_URL}?format=json&lat=${lat}&lon=${lng}&addressdetails=1&namedetails=1`,
                {
                    headers: { 'User-Agent': CONFIG.USER_AGENT }
                }
            );
            if (!response.ok) throw new Error(`HTTP error: ${response.status}`);
            const result = await response.json();

            if (result.address || result.namedetails) {
                const address = result.address || {};
                const namedetails = result.namedetails || {};
                const landmark = namedetails.name || address.poi || address.attraction || address.building || '';
                const city = address.city || address.town || address.village || address.state || '';
                const country = address.country || '';
                const formattedAddress = this.sanitizeText(
                    landmark || (city && country ? `${city}, ${country}` : result.display_name || 'Unknown Location')
                );
                this.input.value = formattedAddress;
                this.input.classList.remove('is-invalid');
                this.input.setAttribute('aria-invalid', 'false');
                this.updateMarker(lat, lng);
                console.debug('Saved address:', formattedAddress, 'from coordinates:', lat, lng);
            } else {
                this.input.classList.add('is-invalid');
                this.input.setAttribute('aria-invalid', 'true');
                this.input.value = '';
                console.warn('No address found for coordinates:', lat, lng);
            }
        } catch (error) {
            console.error('Error with reverse geocoding:', error);
            this.input.classList.add('is-invalid');
            this.input.setAttribute('aria-invalid', 'true');
            this.input.value = '';
        }
    }

    updateMarker(lat, lon) {
        if (!this.isMapInitialized) return;
        this.map.setView([lat, lon], 15);
        if (this.marker) {
            this.marker.setLatLng([lat, lon]);
        } else {
            this.marker = L.marker([lat, lon]).addTo(this.map);
        }
    }

    addEventListeners() {
        this.input.addEventListener('input', this.debounce((e) => this.fetchSuggestions(e.target.value), CONFIG.DEBOUNCE_DELAY));
        this.input.addEventListener('blur', () => setTimeout(() => (this.suggestionsContainer.innerHTML = ''), CONFIG.BLUR_DELAY));
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault(); // Allow form submission if needed
            }
        });
        if (this.isMapInitialized) {
            this.map.on('click', (e) => this.handleMapClick(e));
        }
    }
}

function initialize() {
    const autocomplete = new TournamentLocationAutocomplete();
    autocomplete.initialize();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
} else {
    initialize();
}