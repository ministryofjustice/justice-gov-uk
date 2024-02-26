export interface Wp {
  i18n: typeof import("@wordpress/i18n")
  plugins: typeof import("@wordpress/plugins")
  components: typeof import("@wordpress/components")
  compose: typeof import("@wordpress/compose")
  data: typeof import("@wordpress/data") 
  editPost: typeof import("@wordpress/edit-post") 
  blocks:  typeof import("@wordpress/blocks")
  element: typeof import("@wordpress/element")
}

interface MetaField {
  name: string
  label: string
  settings :{
    type: 'boolean' | 'string'
  }
}

interface MetaFieldValues {
  [key: string]: string | boolean
}

interface MetaGroup {
  name: string
  title: string
  fields: MetaField[]
}

declare global {
  var wp: Wp
  const justiceBlockEditorLocalized: MetaGroup[]
}
