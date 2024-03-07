export interface SimpleGutemField {
  post_type: string
  control: string
  label: string
  panel: string
  default: any
  conditions: {
    target: string
    operator: '===' | '!==' | 'INTERSECTS' | 'NOT INTERSECTS'
    value: string
  }[]
}

interface SimpleGutenFieldsData{
  [key: string]: SimpleGutemField[]
} 

declare global {
  const sgf_data: SimpleGutenFieldsData
}
