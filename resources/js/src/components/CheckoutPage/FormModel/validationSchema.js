import * as Yup from 'yup';
import moment from 'moment';
import axios from 'axios';
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

const personalNumberChecks = new Map();

const personalNumberExists = value => {
  if (!personalNumberChecks.has(value)) {
    const check = axios
      .post('/api/registration/check-personal-number', { kids_personal_number: value }, { timeout: 15000 })
      .then(response => Boolean(response.data.exists))
      .catch(error => {
        personalNumberChecks.delete(value);
        throw error;
      });

    personalNumberChecks.set(value, check);
  }

  return personalNumberChecks.get(value);
};

export default learningStartDate => [
  Yup.object().shape({
    [municipality.name]: Yup.string().required(`${municipality.requiredErrorMsg}`),
    [gardenName.name]: Yup.string().required(`${gardenName.requiredErrorMsg}`),
    [group.name]: Yup.string().required(`${group.requiredErrorMsg}`)
  }),
  Yup.object().shape({
    [kidsId.name]: Yup.string()
      .required(`${kidsId.requiredErrorMsg}`)
      .matches(/^\d{11}$/, `${kidsId.legthErrorMsg}`)
      .test('unique-personal-number', 'ამ პირადი ნომრით ბავშვი უკვე რეგისტრირებულია.', function (value) {
        if (!/^\d{11}$/.test(value || '')) return true;
        return personalNumberExists(value)
          .then(exists => !exists)
          .catch(() => this.createError({ message: 'პირადი ნომრის შემოწმება ვერ მოხერხდა. სცადეთ ხელახლა.' }));
      }),
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
    [mobileNumber.name]: Yup.string().required(mobileNumber.requiredErrorMsg).matches(/^5\d{8}$/, 'მობილურის ნომერი უნდა შედგებოდეს 9 ციფრისგან და იწყებოდეს 5-ით.'),
    [email.name]: Yup.string().nullable().email(email.notValidErrorMsg)
  })
];
