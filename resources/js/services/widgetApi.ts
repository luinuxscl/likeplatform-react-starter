import axios from 'axios';

/**
 * API service para datos de widgets
 */
export const widgetApi = {
    /**
     * Obtener datos de estadísticas
     */
    async getStats() {
        const response = await axios.get('/api/widgets/data/stats');
        return response.data;
    },

    /**
     * Obtener datos de actividad
     */
    async getActivity(period: 'week' | 'month' = 'week') {
        const response = await axios.get('/api/widgets/data/activity', {
            params: { period },
        });
        return response.data;
    },

    /**
     * Obtener datos de objetivos
     */
    async getGoals(dailyGoal: number = 350) {
        const response = await axios.get('/api/widgets/data/goals', {
            params: { daily_goal: dailyGoal },
        });
        return response.data;
    },

    /**
     * Obtener datos de bienvenida
     */
    async getWelcome() {
        const response = await axios.get('/api/widgets/data/welcome');
        return response.data;
    },

    /**
     * Limpiar caché de datos
     */
    async clearCache() {
        const response = await axios.post('/api/widgets/data/cache/clear');
        return response.data;
    },
};
