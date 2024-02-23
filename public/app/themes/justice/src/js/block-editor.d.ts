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

export interface MetaGroup {
  name: string
  title: string
  fields: MetaField[]
}

declare global {
  const justiceBlockEditorLocalized: MetaGroup[]
}
