import React from 'react';
import { Grid, Typography } from '@material-ui/core';
import { InputField, DatePickerField } from '../../FormFields';

export default function PaymentForm(props) {
  const { formField } = props;

  const model = {
    id: formField[props.gender + 'Id'],
    firstName: formField[props.gender + 'FirstName'],
    lastName: formField[props.gender + 'LastName'],
  };

  return (
    <React.Fragment>
      <div className="registration-section-heading"><span>{props.gender === 'kids' ? '02' : props.gender === 'mothers' ? '03' : '04'}</span><div><Typography variant="h5">{props.gender === 'kids' ? 'ბავშვის ინფორმაცია' : props.gender === 'mothers' ? 'დედის ინფორმაცია' : 'მამის ინფორმაცია'}</Typography><p>მონაცემები მიუთითეთ პირადობის დამადასტურებელი დოკუმენტის შესაბამისად.</p></div></div>
      <Grid container spacing={3}>
        {/* პირადი ნომერი */}
        <Grid item xs={12}>
          <InputField
            isNumber={true}
            name={model.id.name}
            fullWidth
            label={model.id.label}
            setFieldValue={props.setFieldValue}
          />
        </Grid>

        {/* სახელი */}
        <Grid item xs={12} md={6}>
          <InputField
            name={model.firstName.name}
            fullWidth
            label={model.firstName.label}
            setFieldValue={props.setFieldValue}
          />
        </Grid>

        {/* გვარი */}
        <Grid item xs={12} md={6}>
          <InputField
            name={model.lastName.name}
            fullWidth
            label={model.lastName.label}
            setFieldValue={props.setFieldValue}
          />
        </Grid>

        {/* დაბადების თარიღი – მხოლოდ ბავშვის შემთხვევაში */}
        {props.gender === 'kids' && (
          <Grid item xs={12}>
            <DatePickerField
              name={formField.birthDate.name}
              label={formField.birthDate.label}
              fullWidth
            />
          </Grid>
        )}
      </Grid>
    </React.Fragment>
  );
}
