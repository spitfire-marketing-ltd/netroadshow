<template>
  <label class="file-select">
    <div class="select-button">

        <v-icon class="mdi-24px" color="secondary" left
          >mdi-plus-circle</v-icon
        >
        <span v-if="value" class="secondary-text ">Selected File: {{value.name}}</span>
        <span v-else class="secondary-text ">Select File to import</span>


    </div>
    <input ref="cvs" type="file" @change="handleFileChange"/>
  </label>
</template>

<script>
export default {
  props: {
    value: File
  },

  methods: {
    handleFileChange(e) {
      this.$emit('input', e.target.files[0])
    }
  }
}
</script>

<style scoped>
.file-select > .select-button {
  cursor: pointer;
  text-align: center;
}
.file-select > .select-button span {
  font-weight: bold;
  font-size: 0.80rem;
letter-spacing: 0.03rem;
}

.file-select > input[type="file"] {
  display: none;
}
</style>
