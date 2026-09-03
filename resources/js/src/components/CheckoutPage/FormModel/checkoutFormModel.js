export default {
  formId: 'checkoutForm',
  formField: {
    priority: {
      name: 'priority_id',
      label: 'პრიორიტეტი',
      requiredErrorMsg: 'შეავსეთ პრიორიტეტის ველი!'
    },
    municipality: {
      name: 'municipality_id',
      label: 'მუნიციპალიტეტი*',
      requiredErrorMsg: 'შეავსეთ რაიონის ველი!'
    },
    gardenName: {
      name: 'kindergarten_id',
      label: 'ბაღის დასახელება / ნომერი*',
      requiredErrorMsg: 'შეავსეთ ბაღის ველი!'
    },
    group: {
      name: 'group_id',
      label: 'ჯგუფი*',
      requiredErrorMsg: 'ჯგუფის ველი ცარიელია, ან ბაღში არ არის თავისუფალი ადგილი!'
    },
    kidsId: {
      name: 'kids_personal_number',
      label: 'პირადი №*',
      requiredErrorMsg: 'შეავსეთ ბავშვის პირადი № ველი!',
      legthErrorMsg: 'პირადობის ნომერი უნდა შედგებოდეს 11 - ციფრისგან!'
    },
    kidsFirstName: {
      name: 'kids_first_name',
      label: 'სახელი*',
      requiredErrorMsg: 'შეავსეთ ბავშვის სახელის ველი!'
    },
    kidsLastName: {
      name: 'kids_last_name',
      label: 'გვარი*',
      requiredErrorMsg: 'შეავსეთ ბავშვის გვარის ველი!'
    },
    
     birthDate: {
  name: 'birth_date',
  label: 'დაბადების თარიღი*',
  requiredErrorMsg: 'შეავსეთ დაბადების თარიღის ველი!'
},
 
    mothersId: {
      name: 'mother_personal_number',
      label: 'პირადი №*',
      requiredErrorMsg: 'შეავსეთ დედის პირადი № ველი!',
      legthErrorMsg: 'პირადობის ნომერი უნდა შედგებოდეს 11 - ციფრისგან!'
    },
    mothersFirstName: {
      name: 'mother_first_name',
      label: 'სახელი*',
      requiredErrorMsg: 'შეავსეთ დედის სახელის ველი!'
    },
    mothersLastName: {
      name: 'mother_last_name',
      label: 'გვარი*',
      requiredErrorMsg: 'შეავსეთ დედის გვარის ველი!'
    },
    fathersId: {
      name: 'father_personal_number',
      label: 'პირადი №*',
      requiredErrorMsg: 'შეავსეთ მამის პირადი № ველი!',
      legthErrorMsg: 'პირადობის ნომერი უნდა შედგებოდეს 11 - ციფრისგან!'
    },
    fathersFirstName: {
      name: 'father_first_name',
      label: 'სახელი*',
      requiredErrorMsg: 'შეავსეთ მამის სახელის ველი!'
    },
    fathersLastName: {
      name: 'father_last_name',
      label: 'გვარი*',
      requiredErrorMsg: 'შეავსეთ მამის გვარის ველი!'
    },
    mobileNumber: {
      name: 'mobile_number',
      label: 'მობილურის ნომერი*',
      notValidErrorMsg: 'მობილურის ნომერი არ არის ვალიდური!',
      requiredErrorMsg: 'შეავსეთ მობილურის ველი!'
    },
    email: {
      name: 'email',
      label: 'ელ-ფოსტა',
      notValidErrorMsg: 'ელ-ფოსტა არ არის ვალიდური!',
      requiredErrorMsg: 'შეავსეთ ელ-ფოსტის ველი!'
    }
  }
};






