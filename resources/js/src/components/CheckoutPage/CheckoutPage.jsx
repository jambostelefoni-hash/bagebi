import React, { useState, useEffect } from 'react';
import { Stepper, Step, StepLabel, Button, CircularProgress } from '@material-ui/core';

import { Formik, Form } from 'formik'

import validationSchema from './FormModel/validationSchema';
import checkoutFormModel from './FormModel/checkoutFormModel';
import formInitialValues from './FormModel/formInitialValues';

import axios from 'axios'

import useStyles from './styles';

import AddressForm from './Forms/AddressForm';
import PaymentForm from './Forms/PaymentForm';
import ReviewOrder from './Forms/ReviewOrder';
import CheckoutSuccess from './CheckoutSuccess';

const domain = location.protocol + '//' + location.host;
const steps = ['ბაღი', 'ბავშვი', 'დედა', 'მამა', 'დამატებითი ინფორმაცია']
const { formId, formField } = checkoutFormModel;

function _renderStepContent(step, setFieldValue, dataObject, setDataObject) {
  switch (step) {
    case 0:
      return <AddressForm formField={formField} setFieldValue={setFieldValue} dataObject={dataObject} setDataObject={setDataObject} />;
    case 1:
      return <PaymentForm key="kids" gender="kids" formField={formField} setFieldValue={setFieldValue} />;
    case 2:
      return <PaymentForm key="mothers" gender="mothers" formField={formField} setFieldValue={setFieldValue} />;
    case 3:
      return <PaymentForm key="fathers" gender="fathers" formField={formField} setFieldValue={setFieldValue} />;
    case 4:
      return <ReviewOrder formField={formField} setFieldValue={setFieldValue} />;
    default:
      return <div>Not Found</div>;
  }
}

export default function CheckoutPage() {
  const classes = useStyles();
  const [mounted, setMounted] = useState(false)
  const [loadError, setLoadError] = useState(false);
  const [loadAttempt, setLoadAttempt] = useState(0);
  const [activeStep, setActiveStep] = useState(0);
  const isLastStep = activeStep === steps.length - 1;
  const [responseData, setResponseData] = useState({})
  const [formStatus, setFormStatuss] = useState('')
  
  const dataObjectSetter = {
    municipalities : [],
    kindergartens: [],
    group_ranges: [],
    learning_start_date: null
  }

  const [dataObject, setDataObject] = useState(dataObjectSetter)
  const currentValidationSchema = validationSchema(dataObject.learning_start_date)[activeStep];

  useEffect(() => {
    let active = true;
    setLoadError(false);
    axios.post(`${domain}/api/data-object`, {}, { timeout: 15000 })
      .then(({ data }) => {
        if (active) {
          setDataObject(old => ({ ...old, ...data }));
          setMounted(true);
        }
      })
      .catch(() => { if (active) setLoadError(true); });
    return () => { active = false; };
  }, [loadAttempt]);

  async function _submitForm(values, actions) {
    try {
      const res = await axios.post(`${domain}/api/registration`, values, { timeout: 30000 });
      setResponseData(res.data);
      setFormStatuss(res.data.status);
      if (res.data.status === 'success') setActiveStep(activeStep + 1);
    } catch (error) {
      const errors = error.response?.data?.errors;
      setResponseData({ errors: errors ? Object.values(errors).flat() : ['პასუხის მიღება ვერ მოხერხდა. ხელახლა გაგზავნამდე შეამოწმეთ განაცხადის სტატუსი.'] });
      setFormStatuss('errors');
    } finally {
      actions.setSubmitting(false);
    }
  }

  async function _handleSubmit(values, actions) {
    if (isLastStep) {
      await _submitForm(values, actions);
    } else {
      if (activeStep === 1) {
        try {
          const response = await axios.post(`${domain}/api/registration/check-personal-number`, {
            kids_personal_number: values.kids_personal_number
          }, { timeout: 15000 });

          if (response.data.exists) {
            actions.setFieldError('kids_personal_number', 'ამ პირადი ნომრით ბავშვი უკვე რეგისტრირებულია.');
            actions.setFieldTouched('kids_personal_number', true, false);
            actions.setSubmitting(false);
            return;
          }
        } catch (error) {
          actions.setFieldError('kids_personal_number', 'პირადი ნომრის შემოწმება ვერ მოხერხდა. სცადეთ ხელახლა.');
          actions.setFieldTouched('kids_personal_number', true, false);
          actions.setSubmitting(false);
          return;
        }
      }

      setActiveStep(activeStep + 1);
      actions.setTouched({});
      actions.setSubmitting(false);
    }
  }

  function _handleBack() {
    setActiveStep(activeStep - 1);
  }

  if (!mounted) {
    return <div role={loadError ? 'alert' : 'status'} className={classes.closed}>
      {loadError ? 'ფორმის მონაცემები ვერ ჩაიტვირთა. სცადეთ ხელახლა.' : 'სარეგისტრაციო ფორმა იტვირთება…'}
      {loadError && <Button onClick={() => setLoadAttempt(value => value + 1)}>ხელახლა ცდა</Button>}
    </div>;
  }

  if (dataObject && dataObject.setting && !dataObject.setting.object.isRegistrationStart) {
    return <div className={classes.closed}>რეგისტრაცია ამ ეტაპზე დახურულია.</div>;
  }

  return (
    <div className={classes.formShell}>
          <div className={classes.intro}>
            <div className={classes.introCopy}><strong>{steps[activeStep] || 'დასრულებულია'}</strong><span>შეავსეთ ინფორმაცია ზუსტად ისე, როგორც ოფიციალურ დოკუმენტებშია.</span></div>
            <span className={classes.progressText}>{Math.min(activeStep + 1, steps.length)} / {steps.length} ეტაპი</span>
          </div>
          <Stepper activeStep={activeStep} className={classes.stepper} alternativeLabel>
            {steps.map(label => (
              <Step key={label}>
                <StepLabel>{label}</StepLabel>
              </Step>
            ))}
          </Stepper>
      {activeStep === steps.length && formStatus == 'success' ? (
        <CheckoutSuccess responseData={responseData}/>
      ) : (
        <React.Fragment>
                { formStatus == 'errors' && responseData.errors
                  ? responseData.errors.map((object, i) => <div className={classes.errorBox} key={i}>{object}</div>)
                  : ''
                }
                <Formik
                  initialValues={formInitialValues}
                  validationSchema={currentValidationSchema}
                  onSubmit={_handleSubmit}
                >
                {({ isSubmitting, setFieldValue }) => (
                  <Form id={formId}>
                    <div className={classes.section}>{_renderStepContent(activeStep, setFieldValue, dataObject, setDataObject)}</div>
                    <div className={classes.buttons}>
                      {activeStep !== 0 && (
                        <Button onClick={_handleBack} className={classes.buttonSecondary}>
                          უკან
                        </Button>
                      )}
                      <div className={classes.wrapper}>
                        <Button
                          disabled={isSubmitting}
                          type="submit"
                          variant="contained"
                          color="primary"
                          className={classes.buttonPrimary}>
                          {isLastStep ? 'რეგისტრაციის დასრულება' : 'შემდეგი →'}
                        </Button>
                        {isSubmitting && (
                          <CircularProgress
                            size={24}
                            className={classes.buttonProgress}
                          />
                        )}
                      </div>
                    </div>
                  </Form>
                )}
          </Formik>
        </React.Fragment>
      )}
    </div>
  );
}
