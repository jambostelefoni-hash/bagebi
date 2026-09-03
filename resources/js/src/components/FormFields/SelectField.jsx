import React from 'react';
import PropTypes from 'prop-types';
import { at } from 'lodash';
import { useField } from 'formik';
import {
  InputLabel,
  FormControl,
  Select,
  MenuItem,
  FormHelperText
} from '@material-ui/core';

function SelectField(props) {
  const { label, data, handlechange, objName, ...rest } = props;
  const [field, meta, { setValue, setFieldValue }] = useField(props);
  const { value: selectedValue } = field;
  const [touched, error] = at(meta, 'touched', 'error');
  const isError = touched && error && true;

  function _renderHelperText() {
    if (isError) {
      return <FormHelperText>{error}</FormHelperText>;
    }
  }

  function localhandler (e, objName) {
    setValue(e.target.value ? e.target.value : '')
    handlechange(e.target.value, objName)

  }

  return (
    <FormControl {...rest} error={isError}>
      <InputLabel>{label}</InputLabel>
      <Select {...field} onChange={ (e) => localhandler(e, objName) } value={selectedValue ? selectedValue : ''}>
        <MenuItem value=''>--- აირჩიეთ ---</MenuItem>
        {data.map((value, index) => (
          <MenuItem key={index} value={value.id}>
            {!objName ? value.range : value.name}
          </MenuItem>
        ))}
      </Select>
      {_renderHelperText()}
    </FormControl>
  );
}

SelectField.defaultProps = {
  data: [],
  objName: ''
};

SelectField.propTypes = {
  data: PropTypes.array,
  handlechange: PropTypes.func,
  objName: PropTypes.string
};

export default SelectField;



