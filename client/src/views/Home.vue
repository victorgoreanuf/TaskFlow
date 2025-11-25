<template>
    <div class="mt-4 mb-6">

      <!-- The list with project's from the user-->
      <ProjectsList></ProjectsList>

      <button @click="modal = true">
<!--                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>-->
        <span class="text-lg">Create New Project</span>
      </button>


      <!-- Modal for creating a modal -->
      <CreateNewProject v-model="modal" title="Create Project" @save="save">
          <input v-model="projectName" type="text" placeholder="America" >
      </CreateNewProject>
    </div>
</template>

<script>

import {SET_BREADCRUMB} from "@/store/breadcrumbs.store";
import CreateNewProject from "./components/modals/CreateNewProject.vue";
import ProjectsList from "@/views/components/project/ProjectsDisplay.vue";
export default {
  components: {ProjectsList, CreateNewProject},
  data() {
    return {
      projectName: "",
      modal: false
    }
  },
  methods:{
    async save() {
      if (!this.projectName.trim()) {
        alert("Please enter a project name");
        return;
      }

      try {
        // This uses your Vuex store exactly like register/login
        await this.$store.dispatch('project/createProject', this.projectName.trim());

        // Success → close modal
        this.modal = false;
        this.projectName = "";

        await this.$store.dispatch('project/fetchProjects');
        // Later: this.$toast.success(...) or reload projects list

      } catch (error) {
        alert(error.message || "Something went wrong");
      }
    },
  },
  mounted() {
    this.$store.dispatch(SET_BREADCRUMB, [
      {text: this.$t('My Projects'), active: true},
    ]);
  },
}
</script>

<style>

</style>
