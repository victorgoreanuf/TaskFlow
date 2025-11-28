import Vue from 'vue';

// Initial state
const state = {
    list: [],
    currentProject: null
};

const mutations = {
    SET_PROJECTS(state, projects) {
        state.list = projects.data;
    },
    ADD_PROJECT(state, newProject) {
        state.list.unshift(newProject);
    },
    SET_CURRENT_PROJECT(state, projectObject) {
        state.currentProject = projectObject;
    },
    SET_LOGOUT(state){
        state.list = [];
        state.currentProject = null;
    }
};

const actions = {
    async updateTask({ commit }, payload) {
        const { projectSlug, taskId, name, description, due_date } = payload;

        const response = await Vue.prototype.$http.put(`/api/project/${projectSlug}/tasks/${taskId}`, {
            name: name,
            description: description,
            due_date: due_date
        });
        return response.data;
    },
    async moveTask({ commit }, { projectSlug, taskId, columnId, order }) {
        try {
            // Using the specific move endpoint
            await Vue.prototype.$http.patch(`/api/project/${projectSlug}/tasks/${taskId}/move`, {
                column_id: columnId,
                order: order
            });

            // We do NOT refetch the board here to keep the drag smooth.
            // The local state is already updated by vuedraggable.
        } catch (error) {
            console.error("Vuex: Failed to move task", error);
            // Recommended: If API fails, reload board to revert the drag UI
            // dispatch('fetchProjectBySlug', projectSlug);
            throw error;
        }
    },
    // 6. DELETE TASK (Optional but recommended)
    // DELETE /api/project/{slug}/tasks/{taskId}
    async deleteTask({ commit }, { projectSlug, taskId }) {
        await Vue.prototype.$http.delete(`/api/project/${projectSlug}/tasks/${taskId}`);
    },
    // 1. CREATE COLUMN (Sends Title + Color)
    async createColumn({ commit }, { projectSlug, title, color }) {
        try {
            const response = await Vue.prototype.$http.post(`/api/project/${projectSlug}/columns`, {
                title: title,
                color: color // <--- Sending color to backend
            });
            return response.data;
        } catch (error) {
            console.error("Vuex: Failed to create column", error);
            throw error;
        }
    },

    // 2. UPDATE COLUMN (Sends Title + Color)
    async updateColumn({ commit }, { projectSlug, columnId, title, color }) {
        try {
            const response = await Vue.prototype.$http.put(`/api/project/${projectSlug}/columns/${columnId}`, {
                title: title,
                color: color // <--- Sending color to backend
            });
            return response.data;
        } catch (error) {
            console.error("Vuex: Failed to update column", error);
            throw error;
        }
    },

    // 3. DELETE COLUMN
    async deleteColumn({ commit }, { projectSlug, columnId }) {
        try {
            await Vue.prototype.$http.delete(`/api/project/${projectSlug}/columns/${columnId}`);
        } catch (error) {
            console.error("Vuex: Failed to delete column", error);
            throw error;
        }
    },

    // 4. CREATE TASK (Sends Date + Description)
    async createTask({ commit }, payload) {
        const { projectSlug, columnId, name, description, due_date } = payload;

        const response = await Vue.prototype.$http.post(`/api/project/${projectSlug}/tasks`, {
            column_id: columnId,
            name: name,
            description: description,
            due_date: due_date // <--- Sending date to backend
        });

        return response.data;
    },

    // 5. FETCH PROJECT
    async fetchProjectBySlug({ commit }, slug) {
        if (!slug) return;
        try {
            const response = await Vue.prototype.$http.get(`/api/project/${slug}`);
            commit('SET_CURRENT_PROJECT', response.data.data);
            return response.data.data;
        } catch (error) {
            console.error(`Error fetching project board for slug ${slug}:`, error.response || error);
            commit('SET_CURRENT_PROJECT', null);
            throw error;
        }
    },

    async fetchProjects({ commit }) {
        try {
            const response = await Vue.prototype.$http.get('/api/project');
            commit('SET_PROJECTS', response.data);
        } catch (error) {
            console.error('Error fetching projects list:', error);
            throw error;
        }
    },

    async createProject({ commit }, projectName) {
        try {
            const response = await Vue.prototype.$http.post('/api/project', {
                name: projectName
            });
            commit('ADD_PROJECT', response.data.data);
            return response.data.data;
        } catch (error) {
            console.error('Error creating project:', error);
            throw error;
        }
    }
};

const getters = {
    projectsList: state => state.list,
    currentProject: state => state.currentProject,
};

export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};