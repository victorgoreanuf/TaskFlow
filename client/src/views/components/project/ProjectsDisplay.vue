<template>
  <div class="project-list-container">

    <h3 class="list-title">My Projects</h3>

    <ul v-if="projectsList.length" class="project-list">
      <router-link
          v-for="project in projectsList"
          :key="project.slug"
          :to="{ name: 'project-board', params: { id: project.slug } }"
          tag="li"
          class="project-item"
          active-class="active-project"
          @click.native="handleProjectClick(project.slug)"
      >
        <div class="item-content">
            <span class="project-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
            </span>
          <span class="project-name">{{ project.name }}</span>
        </div>

        <span class="chevron-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </span>
      </router-link>
    </ul>

    <div v-else class="empty-state">
      <div class="empty-icon">📂</div>
      <p>No projects found.</p>
      <small>Create one to get started!</small>
    </div>

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
    handleProjectClick(projectId) {
      this.$store.dispatch('project/fetchProjectBySlug', projectId);
    }
  },

  created() {
    if (this.projectsList.length === 0) {
      this.$store.dispatch('project/fetchProjects');
    }
  }
}
</script>

<style scoped>
/* Container styling */
.project-list-container {
  padding: 1.5rem 1rem;
  max-width: 300px; /* Optional: Constrain width if not in a sidebar */
}

.list-title {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6b7280; /* Gray-500 */
  font-weight: 700;
  margin-bottom: 1rem;
  padding-left: 0.5rem;
}

.project-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

/* Individual Item Styling */
.project-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.65rem 0.75rem;
  margin-bottom: 0.25rem;
  border-radius: 0.5rem;
  cursor: pointer;
  color: #374151; /* Gray-700 */
  transition: all 0.2s ease;
  user-select: none; /* Prevents text highlighting */
  text-decoration: none;
}

.item-content {
  display: flex;
  align-items: center;
  overflow: hidden; /* For text truncation */
}

/* Hover State */
.project-item:hover {
  background-color: #f3f4f6; /* Gray-100 */
  color: #111827; /* Gray-900 */
}

/* Icon Container */
.project-icon-wrapper {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border-radius: 6px;
  background-color: #e5e7eb; /* Gray-200 */
  color: #6b7280; /* Gray-500 */
  margin-right: 0.75rem;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

/* Text Styling */
.project-name {
  font-size: 0.9rem;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Chevron (Arrow) Logic */
.chevron-icon {
  opacity: 0;
  transform: translateX(-5px);
  transition: all 0.2s ease;
  color: #9ca3af;
}

.project-item:hover .chevron-icon {
  opacity: 1;
  transform: translateX(0);
}

/* --- ACTIVE STATE (Selected Project) --- */
.project-item.active-project {
  background-color: #eef2ff; /* Indigo-50 */
  color: #4f46e5; /* Indigo-600 */
}

.project-item.active-project .project-icon-wrapper {
  background-color: #4f46e5;
  color: white;
}

.project-item.active-project .chevron-icon {
  opacity: 1;
  color: #4f46e5;
  transform: translateX(0);
}

/* Empty State Styling */
.empty-state {
  text-align: center;
  padding: 2rem 1rem;
  border: 2px dashed #e5e7eb;
  border-radius: 0.75rem;
  color: #6b7280;
}

.empty-icon {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.empty-state p {
  font-weight: 600;
  margin: 0;
  font-size: 0.9rem;
}

.empty-state small {
  font-size: 0.8rem;
  color: #9ca3af;
}
</style>