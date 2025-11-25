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
        // projects is expected to be { data: [...] } from the API
        state.list = projects.data;
    },
    // Mutation to add a new project to the list (used after creation)
    ADD_PROJECT(state, newProject) {
        state.list.unshift(newProject); // Add to the start of the list
    },
    /**
     * Mutation to set the project the user is currently looking at.
     * This now accepts the full project object returned by the API (which includes columns/tasks).
     */
    SET_CURRENT_PROJECT(state, projectObject) {
        state.currentProject = projectObject;
    },
    // Clear state on logout
    SET_LOGOUT(state){
        state.list = [];
        state.currentProject = null;
    }
};

const actions = {
    /**
     * Action to fetch the full project details (including columns and tasks) from the API.
     * @param {string} slug - The project's unique slug from the URL.
     */
    async fetchProjectBySlug({ commit }, slug) {
        if (!slug) return;

        try {
            // API call: GET /api/project/{slug}
            const response = await Vue.prototype.$http.get(`/api/project/${slug}`);

            // Console log the data structure (as requested)
            console.log('API Response (Full Board Data):', response.data.data);

            // Commit the full project object to the currentProject state
            commit('SET_CURRENT_PROJECT', response.data.data);

            return response.data.data;

        } catch (error) {
            console.error(`Error fetching project board for slug ${slug}:`, error.response || error);
            // On error, clear current project state
            commit('SET_CURRENT_PROJECT', null);
            throw error;
        }
    },

    // Renamed local search action (now deprecated in favor of API fetch for board view)
    findProjectInLocalList({ state, commit }, slug) {
        const project = state.list.find(x => x.slug === slug);
        return project;
    },

    /**
     * Action to fetch all projects belonging to the current user.
     */
    async fetchProjects({ commit }) {
        try {
            const response = await Vue.prototype.$http.get('/api/project');
            commit('SET_PROJECTS', response.data);
        } catch (error) {
            console.error('Error fetching projects list:', error);
            throw error;
        }
    },

    /**
     * Action to create a new project.
     */
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