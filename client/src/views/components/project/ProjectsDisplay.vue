<template>
  <div class="project-list-container">

    <ul v-if="projectsList.length" class="space-y-2">
      <router-link
          v-for="project in projectsList"
          :key="project.slug"
          :to="{ name: 'project-board', params: { id: project.slug } }"
          tag="li"
          class="project-item"
          @click.native="handleProjectClick(project.slug)"
      >
        <span class="project-icon">
            <!-- Icon placeholder -->
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m-5 0h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"></path></svg>
        </span>
        <span class="project-name">{{ project.name }}</span>
      </router-link>
    </ul>
    <p v-else class="text-gray-500 p-4 border rounded-lg bg-gray-50">
      No projects yet. Click "Create Project" to begin!
    </p>
  </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
  name: 'ProjectsList',

  computed: {
    ...mapGetters('project', [
      'projectsList',
      'projectCount'
    ])
  },

  methods: {
    /**
     * Dispatches an action to set the current project in the store
     * immediately before navigation occurs.
     */
    handleProjectClick(projectId) {
      // console.log(this.projectsList);
      // Dispatch the action to set the current project in the state
      // This is a synchronous action that uses the local list.
      this.$store.dispatch('project/selectProject', projectId);
    }
  },

  created() {
    // Fetch the list of projects when this component is loaded
    if (this.projectsList.length === 0) {
      this.$store.dispatch('project/fetchProjects');
    }
  }
}
</script>

<style scoped>
/* Basic styling for the list items */
.project-list-container {
  padding: 1rem;
}

.project-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  border-radius: 0.5rem;
  cursor: pointer;
  background-color: #f9fafb; /* Light background */
  color: #1f2937; /* Dark text */
  transition: all 0.2s ease;
  text-decoration: none; /* Router link default */
}

.project-item:hover {
  background-color: #eef2ff; /* Very light indigo on hover */
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

/* Style for the active project (optional) */
.project-item.router-link-exact-active {
  background-color: #4f46e5; /* Primary Indigo */
  color: white;
  font-weight: 600;
}

.project-item.router-link-exact-active .project-icon {
  color: white;
}

.project-icon {
  margin-right: 0.75rem;
  color: #4f46e5; /* Indigo icon color */
}
</style>