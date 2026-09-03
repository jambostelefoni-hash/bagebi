import React from 'react';
import { at } from 'lodash';
import { useField } from 'formik';
import { TextField } from '@material-ui/core';

export default function InputField(props) {
  const { errorText, isNumber, setFieldValue, ...rest } = props;
  const [field, meta] = useField(props);

  function _renderHelperText() {
    const [touched, error] = at(meta, 'touched', 'error');
    if (touched && error) {
      return error;
    }
  }
  
  field.onChange = event => setFieldValue(field.name, isNumber ? event.target.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1') : event.target.value);
  
  return (
    <TextField
      type={isNumber ? 'text' : 'text'}
      error={meta.touched && meta.error && true}
      helperText={_renderHelperText()}
      // { ...(!isNumber ? { pattern: "[A-Za-z]" } : {})}
      {...field}
      {...rest}
    />
  );
}




