import axios from 'axios';

export const generateBusinessPlan = async (data) => {
    try {
        const response = await axios.post('/business-plan/generate', data);
        
        if (response.data.success) {
            return {
                success: true,
                businessPlan: response.data.strategy
            };
        } else {
            throw new Error(response.data.message || 'Failed to generate business plan');
        }
    } catch (error) {
        console.error('Business plan generation error:', error);
        throw new Error(error.response?.data?.message || error.message || 'Failed to generate business plan');
    }
}; 