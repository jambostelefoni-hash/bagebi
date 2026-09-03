import React from 'react';
import { Typography, Grid, Alert } from '@material-ui/core';
import { InputField, DatePickerField } from '../../FormFields'

function CheckoutSuccess(props) {
  return (
    <React.Fragment>
      <React.Fragment>
        <Typography className="title-font" variant="h5" gutterBottom>
          აღსაზრდელი წარმატებით დარეგისტრირდა
        </Typography>
        <Typography variant="subtitle1">
          მადლობას გიხდით ადმინისტრაცია!
        </Typography>
      </React.Fragment>
    </React.Fragment>
  );
}

export default CheckoutSuccess;



