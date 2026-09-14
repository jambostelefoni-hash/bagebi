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

export default learningStartDate => [
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
      .typeError('დაბადების თარიღი არასწორია')
      .required(`${birthDate.requiredErrorMsg}`)
      .max(new Date(), 'დაბადების თარიღი ვერ იქნება მომავალში')
      .test('academic-year-age', 'სასწავლო წლის დაწყებისას ბავშვი უნდა იყოს მინიმუმ 2 წლის და ჯერ არ უნდა ჰქონდეს შესრულებული 6 წელი', value => {
        if (!value || !learningStartDate) return true;
        const birth = moment(value).startOf('day');
        const start = moment(learningStartDate).startOf('day');
        return birth.isSameOrBefore(start.clone().subtract(2, 'years')) && birth.isAfter(start.clone().subtract(6, 'years'));
      })
  }),
  Yup.object().shape({
    [mothersId.name]: Yup.string().nullable().test('len', mothersId.legthErrorMsg, val => !val || val.length === 11),
    [mothersFirstName.name]: Yup.string().nullable(),
    [mothersLastName.name]: Yup.string().nullable()
  }),
  Yup.object().shape({
    [fathersId.name]: Yup.string().nullable().test('len', fathersId.legthErrorMsg, val => !val || val.length === 11),
    [fathersFirstName.name]: Yup.string().nullable(),
    [fathersLastName.name]: Yup.string().nullable()
  }),
  Yup.object().shape({
    [mobileNumber.name]: Yup.string().required(mobileNumber.requiredErrorMsg).matches(/^\d{9}$/, mobileNumber.notValidErrorMsg),
    [email.name]: Yup.string().required(email.requiredErrorMsg).email(email.notValidErrorMsg)
  })
];
