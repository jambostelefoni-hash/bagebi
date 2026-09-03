import React, { useEffect, useState } from 'react';
import { Grid, Typography } from '@material-ui/core';
import { InputField, CheckboxField, SelectField } from '../../FormFields';
import axios from 'axios'


export default function AddressForm(props) {

  const {
    formField: {
      priority,
      municipality,
      gardenName,
      group
    }
  } = props;

  const handlechange = (value, name) => {
    if (name) {
      let dataNewObject = [];
      if (name == 'kindergartens') {
        if (!value) { props.setFieldValue('garden_id', '', false); props.setFieldValue('group_id', '', false) } 
        props.setDataObject(old => ({ ...old, kindergartens : [], group_ranges: [] }))
        dataNewObject = props.dataObject.municipalities.find(elm => elm.id == value)?.kindergartens
      } else if (name == 'group_ranges') {
        props.setFieldValue('group_id', '', false)
        dataNewObject = 
          props.dataObject.kindergartens
            .find(elm => elm.id == value)?.group_age_ranges
            .filter(elm => Number(elm.pivot.space_free) > 0)
      }
      props.setDataObject(old => ({ ...old, [name]: dataNewObject }))
    }
  }

  return (
    <React.Fragment>
      <Typography className="title-font" variant="h5" gutterBottom>
        მდებარეობა
      </Typography>      
      <Grid container spacing={3}>

        {(props.dataObject.setting && props.dataObject.setting.object && props.dataObject.setting.object.isPrioritetiesStart) ? 
          (<Grid item xs={12} >
            <SelectField
              handlechange = { handlechange }
              objName="kindergartens"
              name={priority.name}
              label={priority.label}
              data={props.dataObject.priorities}
              fullWidth
            />
          </Grid>)
          : ''
        }

        <Grid item xs={12} >
          <SelectField
            handlechange = { handlechange }
            objName="kindergartens"
            name={municipality.name}
            label={municipality.label}
            data={props.dataObject.municipalities}
            fullWidth
          />
        </Grid>

        <Grid item xs={12} >
          <SelectField
            handlechange = { handlechange }
            objName="group_ranges"
            name={gardenName.name}
            label={gardenName.label}
            data={props.dataObject.kindergartens}
            fullWidth
          />
        </Grid>
        <Grid item xs={12} >
          <SelectField
            handlechange = { handlechange }
            objName=""
            name={group.name}
            label={group.label}
            data={props.dataObject.group_ranges}
            fullWidth
          />
        </Grid>
        
      </Grid>
    </React.Fragment>
  )
}














