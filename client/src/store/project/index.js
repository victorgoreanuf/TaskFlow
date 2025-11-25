// store/modules/projects.js

import Vue from 'vue';

// Initial state for the projects module
const state = {
    list: [], // Array to hold all the user's projects (summary data)
    currentProject: null // Object to hold details of the project currently being viewed (full board data)
};

const mutations = {
    // Mutation to set the full list of projects
    SET_PROJECTS(state, projects) {
        state.list = projects.data;
    },
    // Mutation to add a new project to the list (used after creation)
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
    // 1. CREATE COLUMN
    async createColumn({ commit }, { projectSlug, title }) {
        try {
            const response = await Vue.prototype.$http.post(`/api/project/${projectSlug}/columns`, {
                title: title
            });
            return response.data;
        } catch (error) {
            console.error("Vuex: Failed to create column", error);
            throw error;
        }
    },

    // 2. UPDATE COLUMN
    async updateColumn({ commit }, { projectSlug, columnId, title }) {
        try {
            const response = await Vue.prototype.$http.put(`/api/project/${projectSlug}/columns/${columnId}`, {
                title: title
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

    // 4. CREATE TASK (Updated with due_date)
    async createTask({ commit }, payload) {
        // Extract due_date here
        const { projectSlug, columnId, name, description, due_date } = payload;

        // Call the endpoint: /api/project/{slug}/tasks
        const response = await Vue.prototype.$http.post(`/api/project/${projectSlug}/tasks`, {
            column_id: columnId,
            name: name,
            description: description,
            due_date: due_date // <--- Pass this to the Laravel Backend
        });

        return response.data;
    },

    /**
     * Action to fetch the full project details
     */
    async fetchProjectBySlug({ commit }, slug) {
        if (!slug) return;

        try {
            const response = await Vue.prototype.$http.get(`/api/project/${slug}`);
            console.log('API Response (Full Board Data):', response.data.data);
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
    projectCount: state => state.list.length,
    currentProject: state => state.currentProject,
};


export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};