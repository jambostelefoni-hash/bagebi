import React from 'react';
import { Typography, Grid } from '@material-ui/core';
import { InputField, CheckboxField, SelectField } from '../../FormFields'

export default function ReviewOrder(props) {

  const {
    formField: { mobileNumber, email }
  } = props

  return (
    <React.Fragment>
      <div className="registration-section-heading"><span>05</span><div><Typography variant="h5">საკონტაქტო ინფორმაცია</Typography><p>ამ მონაცემებზე მიიღებთ რეგისტრაციისა და სტატუსის შეტყობინებებს.</p></div></div>
      <Grid container spacing={3}>
        <Grid item xs={12} md={6}>
          <InputField
            name={mobileNumber.name}
            label={mobileNumber.label}
            setFieldValue={props.setFieldValue}
            fullWidth
          />
        </Grid>
        <Grid item xs={12} md={6}>
          <InputField name={email.name} label={email.label} fullWidth setFieldValue={props.setFieldValue} />
        </Grid>
      </Grid>
    </React.Fragment>
  );
}
