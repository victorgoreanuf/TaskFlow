import Vue from 'vue'
import {initialAbility} from "@/libs/acl/config";

export default {
	namespaced: true, state: {
		checked: false, user: null,
	}, getters: {
		checked(state) {
			return state.checked
		}, authenticated(state) {
			return !!state.user;
		}, user(state) {
			return state.user
		}
	},
	mutations: {
		SET_CHECKED(state) {
			state.checked = true
		},
		SET_USER(state, value) {
			state.user = value;
		},
		SET_PERMISSIONS(state, permissions) {
			if (permissions && permissions.length) {
				Vue.prototype.$ability.update(permissions.map(p => {
					const arr = p.split(' ');
					return {
						action: arr[0],
						subject: arr[1],
					};
				}));
			} else {
				Vue.prototype.$ability.update([initialAbility]);
			}
		}
	},
	actions: {
		async generateCsrfToken() {
			return await Vue.prototype.$http.get('/sanctum/csrf-cookie');
		}, async login({commit, dispatch}, form) {
			await Vue.prototype.$http.post('/api/login', form);
			return dispatch('user');
		}, async register({commit, dispatch}, form) {
            // 1. Send the POST request to the registration endpoint.
            // Your backend (Laravel) will handle validation and user creation.
            await Vue.prototype.$http.post('/api/register', form);

            // 2. 🚨 CRITICAL FIX: Dispatch the 'user' action to load the user's data
            //    and update the Vuex state BEFORE the redirect happens in the component.
            return dispatch('user');

            // Note: If you want to force them to the login page (unauthenticated),
            // you MUST remove the auto-login on the Laravel side (as discussed previously).
            // But since they ARE logged in by the backend, this is the correct frontend step.
        }, async user({commit}) {
			return await Vue.prototype.$http.get('/api/me').then(({data}) => {
				const user = Object.assign({}, data.data);
				delete user.permissions;
				commit('SET_USER', user)
				commit('SET_PERMISSIONS', data.data.permissions)
			}).catch(() => {
				commit('SET_USER', null);
				commit('SET_PERMISSIONS', [])
			}).finally(() => {
				commit('SET_CHECKED', true)
			});
		}, async createProject({ dispatch }, projectName) {
            try {
                // Send the name to your Laravel API
                const response = await Vue.prototype.$http.post('/api/project', {
                    name: projectName
                });

                // Optional: refresh the user/projects if you store them elsewhere
                // await dispatch('user'); // only if you need fresh user data

                return response.data; // return the created project (useful for UI)
            } catch (error) {
                // Throw so component can catch and show error
                const message = error.response?.data?.message || 'Failed to create project';
                throw new Error(message);
            }
        },
        logout({commit}) {
			return Vue.prototype.$http.post('/api/logout').finally(() => {
				commit('SET_USER', null);
				commit('SET_PERMISSIONS', [])
			});
		}
	}
}