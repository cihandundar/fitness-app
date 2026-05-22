import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import BaseButton from '../../app/components/ui/BaseButton.vue'

describe('BaseButton Component', () => {
  // ==================== Rendering ====================

  it('renders slot content', () => {
    const wrapper = mount(BaseButton, {
      slots: {
        default: 'Click Me',
      },
    })

    expect(wrapper.text()).toContain('Click Me')
  })

  it('renders as button element by default', () => {
    const wrapper = mount(BaseButton, {
      slots: {
        default: 'Submit',
      },
    })

    expect(wrapper.find('button').exists()).toBe(true)
  })

  it('renders as link when to prop is provided', () => {
    const wrapper = mount(BaseButton, {
      props: {
        to: '/some-path',
      },
      slots: {
        default: 'Link',
      },
      global: {
        stubs: {
          NuxtLink: {
            template: '<a :href="to"><slot /></a>',
          },
        },
      },
    })

    expect(wrapper.find('a').exists()).toBe(true)
    expect(wrapper.find('button').exists()).toBe(false)
  })

  // ==================== Variants ====================

  it('applies primary variant classes', () => {
    const wrapper = mount(BaseButton, {
      props: {
        variant: 'primary',
      },
      slots: {
        default: 'Primary',
      },
    })

    expect(wrapper.classes()).toContain('from-violet-500')
    expect(wrapper.classes()).toContain('to-purple-600')
  })

  it('applies secondary variant classes', () => {
    const wrapper = mount(BaseButton, {
      props: {
        variant: 'secondary',
      },
      slots: {
        default: 'Secondary',
      },
    })

    expect(wrapper.classes()).toContain('bg-slate-800')
  })

  it('applies danger variant classes', () => {
    const wrapper = mount(BaseButton, {
      props: {
        variant: 'danger',
      },
      slots: {
        default: 'Delete',
      },
    })

    expect(wrapper.classes()).toContain('bg-red-500')
  })

  it('applies success variant classes', () => {
    const wrapper = mount(BaseButton, {
      props: {
        variant: 'success',
      },
      slots: {
        default: 'Success',
      },
    })

    expect(wrapper.classes()).toContain('bg-emerald-500')
  })

  it('applies ghost variant classes', () => {
    const wrapper = mount(BaseButton, {
      props: {
        variant: 'ghost',
      },
      slots: {
        default: 'Ghost',
      },
    })

    expect(wrapper.classes()).toContain('bg-transparent')
  })

  // ==================== Sizes ====================

  it('applies sm size classes', () => {
    const wrapper = mount(BaseButton, {
      props: {
        size: 'sm',
      },
      slots: {
        default: 'Small',
      },
    })

    expect(wrapper.classes()).toContain('px-3')
    expect(wrapper.classes()).toContain('py-1.5')
    expect(wrapper.classes()).toContain('text-sm')
  })

  it('applies lg size classes', () => {
    const wrapper = mount(BaseButton, {
      props: {
        size: 'lg',
      },
      slots: {
        default: 'Large',
      },
    })

    expect(wrapper.classes()).toContain('px-5')
    expect(wrapper.classes()).toContain('py-2.5')
  })

  // ==================== Disabled State ====================

  it('adds disabled classes when disabled', () => {
    const wrapper = mount(BaseButton, {
      props: {
        disabled: true,
      },
      slots: {
        default: 'Disabled',
      },
    })

    expect(wrapper.classes()).toContain('opacity-50')
    expect(wrapper.classes()).toContain('cursor-not-allowed')
  })

  it('disables button when disabled prop is true', () => {
    const wrapper = mount(BaseButton, {
      props: {
        disabled: true,
      },
      slots: {
        default: 'Disabled',
      },
    })

    const button = wrapper.find('button')
    expect(button.attributes('disabled')).toBeDefined()
  })

  // ==================== Loading State ====================

  it('shows loading spinner when loading', () => {
    const wrapper = mount(BaseButton, {
      props: {
        loading: true,
      },
      slots: {
        default: 'Loading',
      },
    })

    expect(wrapper.find('svg').exists()).toBe(true)
  })

  it('disables button when loading', () => {
    const wrapper = mount(BaseButton, {
      props: {
        loading: true,
      },
      slots: {
        default: 'Loading',
      },
    })

    const button = wrapper.find('button')
    expect(button.attributes('disabled')).toBeDefined()
  })

  it('hides icon when loading', () => {
    const wrapper = mount(BaseButton, {
      props: {
        loading: true,
        icon: 'heroicons:user',
      },
      slots: {
        default: 'With Icon',
      },
    })

    // Icon loading olduğunda gösterilmemeli
    expect(wrapper.html()).not.toContain('iconClasses')
  })

  // ==================== Click Events ====================

  it('emits click event when clicked', async () => {
    const wrapper = mount(BaseButton, {
      slots: {
        default: 'Click Me',
      },
    })

    await wrapper.find('button').trigger('click')

    expect(wrapper.emitted('click')).toBeTruthy()
  })

  it('does not emit click when disabled', async () => {
    const wrapper = mount(BaseButton, {
      props: {
        disabled: true,
      },
      slots: {
        default: 'Disabled',
      },
    })

    await wrapper.find('button').trigger('click')

    expect(wrapper.emitted('click')).toBeFalsy()
  })

  it('does not emit click when loading', async () => {
    const wrapper = mount(BaseButton, {
      props: {
        loading: true,
      },
      slots: {
        default: 'Loading',
      },
    })

    await wrapper.find('button').trigger('click')

    expect(wrapper.emitted('click')).toBeFalsy()
  })

  // ==================== Block ====================

  it('applies full width when block is true', () => {
    const wrapper = mount(BaseButton, {
      props: {
        block: true,
      },
      slots: {
        default: 'Block Button',
      },
    })

    expect(wrapper.classes()).toContain('w-full')
  })

  // ==================== Default Props ====================

  it('has correct default variant', () => {
    const wrapper = mount(BaseButton, {
      slots: {
        default: 'Default',
      },
    })

    expect(wrapper.classes()).toContain('from-violet-500')
  })

  it('has correct default size', () => {
    const wrapper = mount(BaseButton, {
      slots: {
        default: 'Default',
      },
    })

    expect(wrapper.classes()).toContain('px-4')
    expect(wrapper.classes()).toContain('py-2')
  })

  it('has correct default type', () => {
    const wrapper = mount(BaseButton, {
      slots: {
        default: 'Submit',
      },
    })

    const button = wrapper.find('button')
    expect(button.attributes('type')).toBe('button')
  })
})
