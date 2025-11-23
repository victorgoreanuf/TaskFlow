// store/modules/projects.js

import Vue from 'vue';

// Initial state for the projects module
const state = {
    list: [], // Array to hold all the user's projects
    currentProject: null // Object to hold details of the project currently being viewed
};

const mutations = {
    // Mutation to set the full list of projects
    SET_PROJECTS(state, projects) {
        console.log(projects);
        state.list = projects;
    },
    // Mutation to add a new project to the list (used after creation)
    ADD_PROJECT(state, newProject) {
        state.list.unshift(newProject); // Add to the start of the list
    },
    // Mutation to set the project the user is currently looking at
    SET_CURRENT_PROJECT(state, project) {
        state.currentProject = project;
    }
};

const actions = {
    /**
     * Action to fetch all projects belonging to the current user.
     */
    async fetchProjects({ commit }) {
        try {
            const response = await Vue.prototype.$http.get('/api/project');
            commit('SET_PROJECTS', response.data.data); // Assuming Laravel returns data in { data: [...] }
        } catch (error) {
            console.error('Error fetching projects:', error);
            // Handle error (e.g., show a toast notification)
        }
    },

    /**
     * Action to create a new project.
     * @param {string} projectName - The name of the project to create.
     */
    async createProject({ commit }, projectName) {
        try {
            // Send the name to your Laravel API
            const response = await Vue.prototype.$http.post('/api/project', {
                name: projectName
            });

            // Commit the new project to the state immediately
            commit('ADD_PROJECT', response.data.data);

            // Return the created project data for the component to use (e.g., for redirect)
            return response.data.data;

        } catch (error) {
            console.error('Error creating project:', error);
            throw error; // Re-throw so the component can handle it
        }
    }
    // Add other actions like updateProject, deleteProject, etc. here
};

const getters = {
    projectsList: state => state.list,
    projectCount: state => state.list.length
};


export default {
    namespaced: true,
    state,
    mutations,
    actions,
    getters
};