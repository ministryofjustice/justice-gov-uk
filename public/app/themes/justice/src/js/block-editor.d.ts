export interface SimpleGutemField {
  post_type: string
  control: string
  label: string
  panel: string
  default: any
  conditions: {
    meta_key: string
    operator: '===' | '!=='
    value: string
  }[]
}

interface SimpleGutenFieldsData{
  [key: string]: SimpleGutemField[]
} 

declare global {
  const sgf_data: SimpleGutenFieldsData
}
