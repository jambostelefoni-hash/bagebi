import * as Yup from 'yup';
import moment from 'moment';
import checkoutFormModel from './checkoutFormModel';

const {
  formField: {
    municipality,
    gardenName,
    group,
    kidsId,
    kidsFirstName,
    kidsLastName,
    birthDate,        // 👈 აქ ჩავამატე
    mothersId,
    mothersFirstName,
    mothersLastName,
    fathersId,
    fathersFirstName,
    fathersLastName,
    mobileNumber,
    email
  }
} = checkoutFormModel;

export default [
  Yup.object().shape({
    [municipality.name]: Yup.string().required(`${municipality.requiredErrorMsg}`),
    [gardenName.name]: Yup.string().required(`${gardenName.requiredErrorMsg}`),
    [group.name]: Yup.string().required(`${group.requiredErrorMsg}`)
  }),
  Yup.object().shape({
    [kidsId.name]: Yup.string().required(`${kidsId.requiredErrorMsg}`).test(
      'len',
      `${kidsId.legthErrorMsg}`,
      val => val && val.length === 11
    ),
    [kidsFirstName.name]: Yup.string().required(`${kidsFirstName.requiredErrorMsg}`),
    [kidsLastName.name]: Yup.string().required(`${kidsLastName.requiredErrorMsg}`),
    [birthDate.name]: Yup.date()
      .required(`${birthDate.requiredErrorMsg}`)
      .max(new Date(), 'დაბადების თარიღი ვერ იქნება მომავალში')
  }),
  // ... mothers, fathers, etc
];
