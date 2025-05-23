import {Alert, Form, Input, Typography} from 'antd'
import React from 'react'
import {t} from 'ttag'
import {LockOutlined, UserOutlined} from "@ant-design/icons"

export default function NamecomConnectorForm() {

    return (
        <>
            <Alert
                message={t`This provider does not provide a list of supported TLD. Please double check if the domain you want to register is supported.`}
                type='warning'
                style={{marginBottom: '2em'}}
            />
            <Form.Item
                label={t`Username`}
                name={['authData', 'username']}
                help={<Typography.Link target='_blank' href='https://www.name.com/account/settings/api'>
                    {t`Retrieve a set of tokens from your customer account on the Provider's website`}
                </Typography.Link>}
            >
                <Input prefix={<UserOutlined/>} autoComplete='off'/>
            </Form.Item>
            <Form.Item
                label={t`API key`}
                name={['authData', 'token']}
            >
                <Input prefix={<LockOutlined/>} autoComplete='off'/>
            </Form.Item>
        </>
    )
}
