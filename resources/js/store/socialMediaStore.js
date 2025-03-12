import { defineStore } from 'pinia';

export const useSocialMediaStore = defineStore('socialMedia', {
    state: () => ({
        facebook: {
            connected: false,
            connecting: false,
            platformId: null,
            pages: []
        },
        instagram: {
            connected: false,
            connecting: false,
            platformId: null,
            accounts: []
        },
        linkedin: {
            connected: false,
            connecting: false,
            platformId: null,
            userPlatformId: null
        },
        lastActiveTimestamp: null,
        sessionValid: false
    }),

    actions: {
        // Initialize the Storage.
        initializeFromStorage() {
            const stored = localStorage.getItem('socialMediaState');
            if (stored) {
                const parsedState = JSON.parse(stored);
                
                // Check if we have a stored timestamp and if it's still valid
                const now = Date.now();
                const lastActive = parsedState.lastActiveTimestamp || 0;
                const sessionTimeout = 60 * 60 * 1000; // 1 hour in milliseconds
                
                // Only restore state if the session is still valid
                if (now - lastActive < sessionTimeout) {
                    this.$patch(parsedState);
                    this.sessionValid = true;
                } else {
                    // Clear expired data
                    this.clearSocialMediaStore();
                    localStorage.removeItem('socialMediaState');
                }
            }
            
            // Update the timestamp
            this.updateTimestamp();
        },

        // Persist the Storage.
        persistToStorage() {
            // Update timestamp before storing
            this.lastActiveTimestamp = Date.now();
            
            localStorage.setItem('socialMediaState', JSON.stringify({
                facebook: this.facebook,
                linkedin: this.linkedin,
                instagram: this.instagram,
                lastActiveTimestamp: this.lastActiveTimestamp
            }));
        },
        
        // Set the Facebook Connection.
        setFacebookConnection(data) {
            this.facebook = {
                connected: true,
                connecting: false,
                platformId: data.platform_id,
                pages: data.pages || []
            };

            // Persist to localStorage
            this.persistToStorage();
        },

        // Set the Linkedin Connection.
        setLinkedinConnection(data) {
            this.linkedin = {
                connected: true,
                connecting: false,
                platformId: data.platform_id,
                userPlatformId: data.user_platform_id
            };
            this.persistToStorage();
        },
        setInstagramConnection(data) {
            this.instagram = {
                connected: true,
                connecting: false,
                platformId: data.platform_id,
                accounts: data.accounts || []
            };
            this.persistToStorage();
        },
        clearInstagramConnection() {
            this.instagram = {
                connected: false,
                connecting: false,
                platformId: null,
                accounts: []
            };
            this.persistToStorage();
        },

        // Clear the Facebook Connection.
        clearFacebookConnection() {
            this.facebook = {
                connected: false,
                connecting: false,
                platformId: null,
                pages: []
            };
            this.persistToStorage();
        },

        // Clear the Linkedin Connection.
        clearLinkedinConnection() {
            this.linkedin = {
                connected: false,
                connecting: false,
                platformId: null,
                userPlatformId: null
            };
            this.persistToStorage();
        },

        // Clear the Social Media Store on Logout.
        clearSocialMediaStore() {
            this.facebook = {
                connected: false,
                connecting: false,
                platformId: null,
                pages: []
            };
            this.linkedin = {
                connected: false,
                connecting: false,
                platformId: null,
                userPlatformId: null
            };
            this.instagram = {
                connected: false,
                connecting: false,
                platformId: null,
                accounts: []
            };
            this.persistToStorage();
        },

        updateTimestamp() {
            this.lastActiveTimestamp = Date.now();
            this.persistToStorage();
        },

        validateSession() {
            // Check if user is authenticated via web route instead of API
            return fetch('/check-auth', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            })
            .then(response => {
                if (!response.ok) {
                    this.clearSocialMediaStore();
                    this.sessionValid = false;
                    return false;
                }
                this.sessionValid = true;
                this.updateTimestamp();
                return true;
            })
            .catch(() => {
                this.clearSocialMediaStore();
                this.sessionValid = false;
                return false;
            });
        }
    },

    getters: {
        isConnected: (state) => (platform) => {
            return state[platform]?.connected || false;
        }
    }
});
