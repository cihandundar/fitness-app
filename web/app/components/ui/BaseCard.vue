<template>
  <div :class="cardClasses" v-bind="$attrs">
    <!-- Card Header -->
    <div v-if="$slots.header || title" class="flex items-center justify-between p-6 border-b" :class="headerClasses">
      <div class="flex-1">
        <h3 v-if="title" class="text-lg font-semibold" :class="titleClasses">{{ title }}</h3>
        <p v-if="subtitle" class="text-sm mt-1" :class="subtitleClasses">{{ subtitle }}</p>
      </div>
      <slot name="headerActions" />
    </div>

    <!-- Card Body -->
    <div :class="bodyClasses">
      <slot />
    </div>

    <!-- Card Footer -->
    <div v-if="$slots.footer" class="p-6 border-t" :class="footerClasses">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

export interface CardProps {
  variant?: 'default' | 'bordered' | 'elevated' | 'glass'
  padding?: 'none' | 'sm' | 'md' | 'lg'
  title?: string
  subtitle?: string
  hoverable?: boolean
  clickable?: boolean
}

const props = withDefaults(defineProps<CardProps>(), {
  variant: 'default',
  padding: 'md',
  hoverable: false,
  clickable: false,
})

const cardClasses = computed(() => {
  const classes = ['rounded-2xl transition-all duration-200']

  switch (props.variant) {
    case 'default':
      classes.push('bg-slate-900/50')
      break
    case 'bordered':
      classes.push('bg-slate-900/50 border border-slate-800/50')
      break
    case 'elevated':
      classes.push('bg-slate-900/50 border border-slate-800/50 shadow-xl')
      break
    case 'glass':
      classes.push('bg-slate-900/30 backdrop-blur-xl border border-slate-800/30')
      break
  }

  if (props.hoverable) {
    classes.push('hover:border-violet-500/30 hover:shadow-lg hover:shadow-violet-500/5')
  }

  if (props.clickable) {
    classes.push('cursor-pointer hover:scale-[1.02]')
  }

  return classes.join(' ')
})

const headerClasses = computed(() => {
  return props.variant === 'glass' ? 'border-slate-800/30' : 'border-slate-800/50'
})

const footerClasses = computed(() => {
  return props.variant === 'glass' ? 'border-slate-800/30' : 'border-slate-800/50'
})

const titleClasses = computed(() => {
  return 'text-white'
})

const subtitleClasses = computed(() => {
  return 'text-slate-400'
})

const bodyClasses = computed(() => {
  const classes = []

  switch (props.padding) {
    case 'none':
      break
    case 'sm':
      classes.push('p-4')
      break
    case 'md':
      classes.push('p-6')
      break
    case 'lg':
      classes.push('p-8')
      break
  }

  return classes.join(' ')
})
</script>
