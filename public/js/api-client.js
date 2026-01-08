const API_BASE_URL = 'http://localhost/hackathon/api';

class APIClient {
    constructor() {
        this.isOnline = navigator.onLine;
        this.syncQueue = this.getQueue();

        // Écouter les changements de connexion
        window.addEventListener('online', () => {
            this.isOnline = true;
            console.log('✅ Connexion rétablie');
            this.syncAll();
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            console.log('⚠️ Mode hors ligne');
        });
    }

    // ================== GESTION DE LA QUEUE OFFLINE ==================

    getQueue() {
        return JSON.parse(localStorage.getItem('sync_queue') || '[]');
    }

    addToQueue(action, data) {
        const queue = this.getQueue();
        queue.push({
            id: crypto.randomUUID(),
            action,
            data,
            timestamp: new Date().toISOString()
        });
        localStorage.setItem('sync_queue', JSON.stringify(queue));
    }

    clearQueue() {
        localStorage.removeItem('sync_queue');
    }

    // ================== MÉTHODE HTTP GÉNÉRIQUE ==================

    async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}${endpoint}`;

        const config = {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        };

        if (options.body) {
            config.body = JSON.stringify(options.body);
        }

        try {
            const response = await fetch(url, config);
            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Erreur API');
            }

            return { success: true, data };

        } catch (error) {
            console.error('Erreur API:', error);
            return { success: false, error: error.message };
        }
    }

    // ================== PROFILS ==================

    async createProfile(profileData) {
        if (!this.isOnline) {
            // Sauvegarder localement
            localStorage.setItem('eautrack_profile', JSON.stringify({
                ...profileData,
                id: Date.now(), // ID temporaire
                offline: true
            }));
            this.addToQueue('create_profile', profileData);
            return { success: true, offline: true };
        }

        const result = await this.request('/profiles.php', {
            method: 'POST',
            body: profileData
        });

        if (result.success) {
            // Sauvegarder localement avec l'ID réel
            localStorage.setItem('eautrack_profile', JSON.stringify(result.data));
        }

        return result;
    }

    async updateProfile(profileId, profileData) {
        if (!this.isOnline) {
            const profile = JSON.parse(localStorage.getItem('eautrack_profile'));
            localStorage.setItem('eautrack_profile', JSON.stringify({
                ...profile,
                ...profileData
            }));
            this.addToQueue('update_profile', { id: profileId, ...profileData });
            return { success: true, offline: true };
        }

        const result = await this.request('/profiles.php', {
            method: 'PUT',
            body: { id: profileId, ...profileData }
        });

        if (result.success) {
            localStorage.setItem('eautrack_profile', JSON.stringify(result.data));
        }

        return result;
    }

    async getProfile(profileId) {
        // Toujours lire depuis localStorage en priorité (plus rapide)
        const cached = localStorage.getItem('eautrack_profile');
        if (cached) {
            return { success: true, data: JSON.parse(cached), cached: true };
        }

        if (!this.isOnline) {
            return { success: false, error: 'Pas de profil local' };
        }

        const result = await this.request(`/profiles.php?id=${profileId}`);

        if (result.success) {
            localStorage.setItem('eautrack_profile', JSON.stringify(result.data));
        }

        return result;
    }

    // ================== CONSOMMATIONS ==================

    async addConsumption(consumptionData) {
        const profile = JSON.parse(localStorage.getItem('eautrack_profile'));

        if (!profile) {
            return { success: false, error: 'Aucun profil trouvé' };
        }

        // Toujours sauvegarder localement d'abord
        const entries = JSON.parse(localStorage.getItem('eautrack_entries') || '[]');
        const newEntry = {
            id: crypto.randomUUID(),
            ...consumptionData,
            user_id: profile.id,
            synced: false,
            created_at: new Date().toISOString()
        };
        entries.push(newEntry);
        localStorage.setItem('eautrack_entries', JSON.stringify(entries));

        // Si online, envoyer au backend
        if (this.isOnline) {
            const result = await this.request('/consumptions.php', {
                method: 'POST',
                body: {
                    user_id: profile.id,
                    activity_id: this.getActivityId(consumptionData.activity),
                    volume: consumptionData.liters,
                    date: consumptionData.date,
                    time: consumptionData.time
                }
            });

            if (result.success) {
                // Marquer comme synchronisé
                newEntry.synced = true;
                newEntry.backend_id = result.data.id;
                localStorage.setItem('eautrack_entries', JSON.stringify(entries));
            } else {
                // Ajouter à la queue de synchronisation
                this.addToQueue('add_consumption', consumptionData);
            }

            return result;
        }

        // Mode offline: ajouter à la queue
        this.addToQueue('add_consumption', consumptionData);
        return { success: true, offline: true, data: newEntry };
    }

    async deleteConsumption(entryId) {
        // Supprimer localement
        let entries = JSON.parse(localStorage.getItem('eautrack_entries') || '[]');
        const entry = entries.find(e => e.id === entryId);
        entries = entries.filter(e => e.id !== entryId);
        localStorage.setItem('eautrack_entries', JSON.stringify(entries));

        // Si online et que l'entrée a un backend_id, supprimer sur le serveur
        if (this.isOnline && entry && entry.backend_id) {
            await this.request('/consumptions.php', {
                method: 'DELETE',
                body: { id: entry.backend_id }
            });
        }

        return { success: true };
    }

    async getConsumptions(userId, date) {
        // Lire depuis localStorage (source de vérité en mode offline-first)
        const entries = JSON.parse(localStorage.getItem('eautrack_entries') || '[]');
        return { success: true, data: entries };
    }

    async getStats(userId) {
        if (!this.isOnline) {
            // Calculer les stats localement
            return this.calculateLocalStats(userId);
        }

        const result = await this.request(`/stats.php?user_id=${userId}`);
        return result;
    }

    // ================== SYNCHRONISATION ==================

    async syncAll() {
        if (!this.isOnline) return;

        const queue = this.getQueue();
        if (queue.length === 0) return;

        console.log(`🔄 Synchronisation de ${queue.length} action(s)...`);

        for (const item of queue) {
            try {
                switch (item.action) {
                    case 'create_profile':
                        await this.createProfile(item.data);
                        break;
                    case 'update_profile':
                        await this.updateProfile(item.data.id, item.data);
                        break;
                    case 'add_consumption':
                        await this.addConsumption(item.data);
                        break;
                }
            } catch (error) {
                console.error('Erreur sync:', error);
            }
        }

        this.clearQueue();
        console.log('✅ Synchronisation terminée');

        // Déclencher un événement pour rafraîchir l'UI
        window.dispatchEvent(new CustomEvent('sync-complete'));
    }

    // ================== HELPERS ==================

    getActivityId(activityKey) {
        const map = {
            'shower': 1,
            'kitchen': 2,
            'dishes': 3,
            'garden': 4
        };
        return map[activityKey] || 1;
    }

    calculateLocalStats(userId) {
        const entries = JSON.parse(localStorage.getItem('eautrack_entries') || '[]');
        const profile = JSON.parse(localStorage.getItem('eautrack_profile'));

        const today = new Date().toISOString().slice(0, 10);
        const todayEntries = entries.filter(e => e.date === today);
        const totalToday = todayEntries.reduce((sum, e) => sum + Number(e.liters), 0);

        return {
            success: true,
            data: {
                total_today: totalToday,
                quota: profile ? .quota_l_day || 0,
                percentage: profile ? .quota_l_day ? Math.round((totalToday / profile.quota_l_day) * 100) : 0,
                entries_count: todayEntries.length
            }
        };
    }
}

// Instance globale
const api = new APIClient();